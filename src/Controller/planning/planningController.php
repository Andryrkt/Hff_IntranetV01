<?php

namespace App\Controller\planning;


use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Entity\planning\PlanningSearch;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\Transformation;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\planning\PlanningSearchType;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends Controller
{
    use Transformation;
    use PlanningTraits;

    private PlanningModel $planningModel;
    private PlanningSearch $planningSearch;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new PlanningModel();
        $this->planningSearch = new PlanningSearch();
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/planning", name="planning_vue")
     * 
     * @return void
     */
    public function listePlanning(Request $request)
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

        $form = self::$validator->createBuilder(
            PlanningSearchType::class,
            $this->planningSearch,
            [
                'method' => 'GET',
                'planningDetaille' => false,
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->planningSearch;

        if ($form->isSubmitted() && $form->isValid()) {
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


        if ($request->query->get('action') !== 'oui') {
            $lesOrvalides = $this->recupNumOrValider($criteria, self::$em);
            $tousLesOrSoumis = $this->allOrs();
            $touslesOrItvSoumis = $this->allOrsItv();

            $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv'], $criteria, $tousLesOrSoumis);

            if (is_array($back)) {
                $backString = TableauEnStringService::orEnString($back);
            } else {
                $backString = '';
            }
            $data = $this->planningModel->recuperationMaterielplanifier($criteria, $lesOrvalides['orAvecItv'], $backString, $touslesOrItvSoumis);
        } else {
            $data = [];
            $back = [];
        }

        $tabObjetPlanning = $this->creationTableauObjetPlanning($data, $back, self::$em);
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);

        $forDisplay = $this->prepareDataForDisplay($fusionResult, $criteria->getMonths() == null ? 3 : $criteria->getMonths());

        // dd($forDisplay);
        $this->logUserVisit('planning_vue'); // historisation du page visité par l'utilisateur

        self::$twig->display('planning/planning.html.twig', [
            'form' => $form->createView(),
            'preparedData' => $forDisplay['preparedData'],
            'uniqueMonths' => $forDisplay['uniqueMonths'],
        ]);
    }

    private function allOrsItv()
    {
        return TableauEnStringService::TableauEnString(',', $this->ditOrsSoumisAValidationRepository->findNumOrItvAll());
    }

    private function allOrs()
    {
        return TableauEnStringService::TableauEnString(',', $this->ditOrsSoumisAValidationRepository->findNumOrAll());
    }

    /**
     * @Route("/export_excel_planning", name= "export_planning")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->sessionService->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);

        $lesOrvalides = $this->recupNumOrValider($planningSearch, self::$em);

        $back = $this->planningModel->backOrderPlanning($lesOrvalides['orSansItv'], $criteria, $this->allOrs());
        $data = $this->planningModel->exportExcelPlanning($planningSearch, $lesOrvalides['orAvecItv']);



        $tabObjetPlanning = $this->creationTableauObjetPlanning($data, $back, self::$em);
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetail($tabObjetPlanning);



        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Agence\Service', 'ID', 'Marque', 'Modèle', 'N°Serie', 'N°Parc', 'Casier', 'Jan', 'Fév', 'Mar',  'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']; // En-têtes des colonnes
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
    public function exportExcel01()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $criteria = $this->sessionService->get('planning_search_criteria');

        $planningSearch = $this->creationObjetCriteria($criteria);

        $lesOrvalides = $this->recupNumOrValider($planningSearch, self::$em);

        $data = $this->planningModel->exportExcelPlanning($planningSearch, $lesOrvalides['orAvecItv']);
        //  dd($data);

        $tabObjetPlanning = $this->creationTableauObjetExport($data);



        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['Agence\Service', 'N°OR-Itv', 'libellé de l\'Itv', 'planification', 'ID', 'Marque', 'Modèle', 'N°Serie', 'N°Parc', 'Casier', 'Mois planning', 'Année planning', 'Statut IPS', 'COMMENTAIRE ICI', 'ACTION']; // En-têtes des colonnes
        foreach ($tabObjetPlanning as $entity) {
            $data[] = [
                $entity->getLibsuc() . ' - ' . $entity->getLibServ(),
                $entity->getOrIntv(),
                $entity->getCommentaire(),
                $entity->getPlan(),
                $entity->getIdMat(),
                $entity->getMarqueMat(),
                $entity->getTypeMat(),
                $entity->getnumSerie(),
                $entity->getnumParc(),
                $entity->getCasier(),
                $entity->getMois(),
                $entity->getAnnee(),
                $entity->getPos()

            ];
        }

        $this->excelService->createSpreadsheet($data);
    }
}
