<?php
namespace App\Controller\planning;

use App\Controller\Controller;
use App\Controller\Traits\PlanningTraits;
use App\Model\planning\PlanningModel;

use App\Entity\planning\PlanningSearch;
use App\Controller\Traits\Transformation;
use App\Entity\dit\DemandeIntervention;
use App\Entity\planning\PlanningMateriel;
use App\Form\planning\MoisAvantType;
use App\Form\planning\PlanningSearchType;
use App\Service\fusionPdf\FusionPdf;
use Dotenv\Parser\Entry;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends Controller
{        
    use Transformation; 
    use PlanningTraits;

        private PlanningModel $planningModel;
        private PlanningSearch $planningSearch;
        
        public function __construct()
        {
            parent::__construct();
            $this->planningModel = new PlanningModel();
            $this->planningSearch = new PlanningSearch();
        }

        /**
         * @Route("/planning", name="planning_vue")
         * 
         * @return void
         */
        public function listePlanning( Request $request)
        {
            //verification si user connecter
            $this->verifierSessionUtilisateur();

            
            //initialisation
            $this->planningSearch
                ->setAnnee(date('Y'))
                ->setFacture('ENCOURS')
                ->setPlan('PLANIFIE')
                ->setInterneExterne('TOUS')
                ->setTypeLigne('TOUETS')
                ->setMonths(3)
            ;

            $form = self::$validator->createBuilder(PlanningSearchType::class,$this->planningSearch,
            [ 
                'method' =>'GET'
            ])->getForm();

            $form->handleRequest($request);
            //initialisation criteria
            $criteria = $this->planningSearch;
            
            if($form->isSubmitted() && $form->isValid())
            {
                  // dd($form->getdata());
                $criteria =  $form->getdata();
            }

            /**
             * Transformation du critère en tableau
             */
            $criteriaTAb = [];
            //transformer l'objet ditSearch en tableau
            $criteriaTAb = $criteria->toArray();
            //recupères les données du criteria dans une session nommé dit_serch_criteria
            $this->sessionService->set('planning_search_criteria', $criteriaTAb);

            
            if($request->query->get('action') !== 'oui') 
            {
                $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);

                $data = $this->planningModel->recuperationMaterielplanifier($criteria,$lesOrvalides);
            } else {
                $data = [];
            }
            
            $tabObjetPlanning = $this->creationTableauObjetPlanning($data);
            // Fusionner les objets en fonction de l'idMat
            $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);

            $forDisplay = $this->prepareDataForDisplay($fusionResult, $criteria->getMonths() == null ? 3 : $criteria->getMonths());
            
            self::$twig->display('planning/planning.html.twig', [
                'form' => $form->createView(),
                'preparedData' => $forDisplay['preparedData'],
                'uniqueMonths' => $forDisplay['uniqueMonths'],
            ]);
        }


    /**
     * @Route("/export_excel_planning", name= "export_planning")
     */
    public function exportExcel(){
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $criteria = $this->sessionService->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);
        
        $lesOrvalides = $this->recupNumOrValider($planningSearch, self::$em);

        $data = $this->planningModel->exportExcelPlanning($planningSearch,$lesOrvalides);

        
        
        $tabObjetPlanning = $this->creationTableauObjetPlanning($data);
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);


        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Agence\Service', 'ID', 'Marque','Modèle', 'N°Serie', 'N°Parc', 'Casier','Jan', 'Fév', 'Mar',  'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov','Déc']; // En-têtes des colonnes
        foreach ($fusionResult as $entity) {
            $row = [
                $entity->getLibsuc() . ' - ' . $entity->getLibServ(),
                $entity->getIdMat(),
                $entity->getMarqueMat(),
                $entity->getTypeMat(),
                $entity->getnumSerie(),
                $entity->getnumParc(),
                $entity->getCasier(),
            ];
        
            // Initialiser les mois avec des valeurs par défaut
            $moisData = array_fill(1, 12, '-');
        
            // Ajouter les données des mois disponibles
            foreach ($entity->getMoisDetails() as $value) {
                if (isset($value['mois'], $value['orIntv']) && $value['mois'] >= 1 && $value['mois'] <= 12) {
                    if ($moisData[$value['mois']] !== '-') {
                        $moisData[$value['mois']] .= "  " . $value['orIntv']; // Ajout d'un saut de ligne et de la nouvelle valeur
                    } else {
                        $moisData[$value['mois']] = $value['orIntv']; // Nouvelle valeur
                    }
                }
            }
        
            // Fusionner les données générales avec celles des mois
            $data[] = array_merge($row, $moisData);
        }
        
        $this->excelService->createSpreadsheet($data);
    }



    
    /**
     * @Route("/export_excel_planning01", name= "export_planning01")
     */
    public function exportExcel01(){
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $criteria = $this->sessionService->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);
        
        $lesOrvalides = $this->recupNumOrValider($planningSearch, self::$em);

        $data = $this->planningModel->exportExcelPlanning($planningSearch,$lesOrvalides);

        
        $tabObjetPlanning = $this->creationTableauObjetExport($data);
       
        

                // Convertir les entités en tableau de données
                $data = [];
                $data[] = ['Agence\Service','N°OR-Itv', 'ID', 'Marque','Modèle', 'N°Serie', 'N°Parc', 'Casier','Mois planning','Statut IPS','COMMENTAIRE ICI','ACTION']; // En-têtes des colonnes
                foreach ($tabObjetPlanning as $entity) {
                    $data[] = [
                        $entity->getLibsuc() . ' - ' . $entity->getLibServ(),
                        $entity->getOrIntv(),
                        $entity->getIdMat(),
                        $entity->getMarqueMat(),
                        $entity->getTypeMat(),
                        $entity->getnumSerie(),
                        $entity->getnumParc(),
                        $entity->getCasier(),
                        $entity->getMois(),
                        $entity->getPos()
                        
                    ];
                }
                
                $this->excelService->createSpreadsheet($data);
    }

    private function creationObjetCriteria(array $criteria): PlanningSearch
    {
        //crée une objet à partir du tableau critère reçu par la session
        $this->planningSearch
            ->setAgence($criteria["agence"])
            ->setAnnee($criteria["annee"])
            ->setInterneExterne($criteria["interneExterne"])
            ->setFacture($criteria["facture"])
            ->setPlan($criteria["plan"])
            ->setDateDebut($criteria["dateDebut"])
            ->setDateFin($criteria["dateFin"])
            ->setNumOr($criteria["numOr"])
            ->setNumSerie($criteria["numSerie"])
            ->setIdMat($criteria["idMat"])
            ->setNumParc($criteria["numParc"])
            ->setAgenceDebite($criteria["agenceDebite"])
            ->setServiceDebite($criteria["serviceDebite"])
            ->setTypeligne($criteria["typeligne"])  
            
        ;

        return $this->planningSearch;
    }

    private function creationTableauObjetExport(array $data):array{

        $objetPlanning = [];
        //Recuperation de idmat et les truc
        foreach ($data as $item ) {
            $planningMateriel = new PlanningMateriel();
            
            //initialisation
                $planningMateriel
                    ->setCodeSuc($item['codesuc'])
                    ->setLibSuc($item['libsuc'])
                    ->setCodeServ($item['codeserv'])
                    ->setLibServ($item['libserv'])
                    ->setIdMat($item['idmat'])
                    ->setMarqueMat($item['markmat'])
                    ->setTypeMat($item['typemat'])
                    ->setNumSerie($item['numserie'])
                    ->setNumParc($item['numparc'])
                    ->setCasier($item['casier'])
                    ->setAnnee($item['annee'])
                    ->setMois($item['mois'])
                    ->setPos($item['slor_pos'])
                    ->setOrIntv($item['orintv'])
                    
                ;
                $objetPlanning[] = $planningMateriel;
        }
        
        return $objetPlanning;
    }

    private function creationTableauObjetPlanning(array $data): array
    {
        $objetPlanning = [];
        //Recuperation de idmat et les truc
        foreach ($data as $item ) {
            $planningMateriel = new PlanningMateriel();
            $ditRepositoryConditionner = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $item['orintv'])[0]]);
            $numDit = $ditRepositoryConditionner->getNumeroDemandeIntervention();
            $migration = $ditRepositoryConditionner->getMigration();
            
            //initialisation
                $planningMateriel
                    ->setCodeSuc($item['codesuc'])
                    ->setLibSuc($item['libsuc'])
                    ->setCodeServ($item['codeserv'])
                    ->setLibServ($item['libserv'])
                    ->setIdMat($item['idmat'])
                    ->setMarqueMat($item['markmat'])
                    ->setTypeMat($item['typemat'])
                    ->setNumSerie($item['numserie'])
                    ->setNumParc($item['numparc'])
                    ->setCasier($item['casier'])
                    ->setAnnee($item['annee'])
                    ->setMois($item['mois'])
                    ->setOrIntv($item['orintv'])
                    ->setQteCdm($item['qtecdm'])
                    ->setQteLiv($item['qtliv'])
                    ->setQteAll($item['qteall'])
                    ->setNumDit($numDit)
                    // ->setNumeroOr($item['numeroor'])
                    ->addMoisDetail($item['mois'], $item['annee'], $item['orintv'], $item['qtecdm'], $item['qtliv'], $item['qteall'], $numDit, $migration, $item['commentaire'])
                ;
                $objetPlanning[] = $planningMateriel;
        }
        return $objetPlanning;
    }

    private function ajoutMoiDetail(array $objetPlanning): array
    {
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = [];
        foreach ($objetPlanning as $materiel) {
            $key = $materiel->getIdMat(); // Utiliser idMat comme clé unique
            if (!isset($fusionResult[$key])) {
                $fusionResult[$key] = $materiel; // Si la clé n'existe pas, on l'ajoute
            } else {
                // Si l'élément existe déjà, on fusionne les détails des mois
                foreach ($materiel->moisDetails as $moisDetail) {

                    $fusionResult[$key]->addMoisDetail(
                        $moisDetail['mois'],
                        $moisDetail['annee'],
                        $moisDetail['orIntv'],
                        $moisDetail['qteCdm'],
                        $moisDetail['qteLiv'],
                        $moisDetail['qteAll'],
                        $moisDetail['numDit'],
                        $moisDetail['migration'],
                        $moisDetail['commentaire']
                    );
                }
                
            }
        }

        return $fusionResult;
    }


    function prepareDataForDisplay(array $data, int $selectedOption): array {
        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $currentMonth = (int)date('n') - 1; // Index du mois actuel (0-11)
        $currentYear = (int)date('Y');
    
        $selectedMonths = [];
    
        // Déterminer les mois à afficher selon l'option choisie
        switch ($selectedOption) {
            case 3: // 3 mois suivant
                for ($i = 0; $i < 4; $i++) {
                    $monthIndex = ($currentMonth + $i) % 12;
                    $yearOffset = intdiv($currentMonth + $i, 12);
                    $year = $currentYear + $yearOffset;
                    $selectedMonths[] = [
                        'month' => $months[$monthIndex],
                        'year' => $year,
                        'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
                    ];
                }
                // Compléter avec les mois précédents
                for ($i = -1; count($selectedMonths) < 12; $i--) {
                    $monthIndex = ($currentMonth + $i + 12) % 12;
                    $yearOffset = intdiv($currentMonth + $i, 12);
                    $year = $currentYear + $yearOffset;
                    array_unshift($selectedMonths, [
                        'month' => $months[$monthIndex],
                        'year' => $year,
                        'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
                    ]);
                }
                break;
    
            case 6: // 6 mois suivant
                for ($i = 0; $i < 7; $i++) {
                    $monthIndex = ($currentMonth + $i) % 12;
                    $yearOffset = intdiv($currentMonth + $i, 12);
                    $year = $currentYear + $yearOffset;
                    $selectedMonths[] = [
                        'month' => $months[$monthIndex],
                        'year' => $year,
                        'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
                    ];
                }
                // Compléter avec les mois précédents
                for ($i = -1; count($selectedMonths) < 12; $i--) {
                    $monthIndex = ($currentMonth + $i + 12) % 12;
                    $yearOffset = intdiv($currentMonth + $i, 12);
                    $year = $currentYear + $yearOffset;
                    array_unshift($selectedMonths, [
                        'month' => $months[$monthIndex],
                        'year' => $year,
                        'key' => sprintf('%04d-%02d', $year, $monthIndex + 1),
                    ]);
                }
                break;
    
            case 9: // Année en cours
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = [
                        'month' => $months[$i],
                        'year' => $currentYear,
                        'key' => sprintf('%04d-%02d', $currentYear, $i + 1),
                    ];
                }
                break;
    
            case 11: // Année suivante
                for ($i = 0; $i < 12; $i++) {
                    $selectedMonths[] = [
                        'month' => $months[$i],
                        'year' => $currentYear + 1,
                        'key' => sprintf('%04d-%02d', $currentYear + 1, $i + 1),
                    ];
                }
                break;
        }
    
        // Filtrer les données en fonction des mois sélectionnés
        $preparedData = [];
        foreach ($data as $item) {
            $moisDetails = property_exists($item, 'moisDetails') && is_array($item->getMoisDetails())
                ? $item->getMoisDetails()
                : [];
    
            $filteredMonths = [];
            foreach ($moisDetails as $detail) {
                if (is_array($detail) && isset($detail['orIntv']) && $detail['orIntv'] !== "-") {
                    $monthIndex = (int)$detail['mois'] - 1;
                    $year = $detail['annee'] ?? '';
    
                    $monthKey = sprintf('%04d-%02d', $year, $monthIndex + 1);
                    if (array_search($monthKey, array_column($selectedMonths, 'key')) !== false) {
                        $filteredMonths[] = [
                            'month' => $months[$monthIndex] ?? '',
                            'year' => $year,
                            'details' => $detail,
                        ];
                    }
                }
            }
    
            $preparedData[] = [
                'libsuc' => $item->getLibsuc() ?? '',
                'libserv' => $item->getLibServ() ?? '',
                'idmat' => $item->getIdMat() ?? '',
                'marqueMat' => $item->getMarqueMat() ?? '',
                'typemat' => $item->getTypeMat() ?? '',
                'numserie' => $item->getNumSerie() ?? '',
                'numparc' => $item->getNumParc() ?? '',
                'casier' => $item->getCasier() ?? '',
                'filteredMonths' => $filteredMonths,
            ];
        }
    
        return [
            'preparedData' => $preparedData,
            'uniqueMonths' => $selectedMonths,
        ];
    }
    
    
    

}