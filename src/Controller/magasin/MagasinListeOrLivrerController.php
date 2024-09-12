<?php


namespace App\Controller\magasin;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Controller\Controller;
use App\Controller\Traits\MagasinTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Form\magasin\MagasinListeOrALivrerSearchType;

class MagasinListeOrLivrerController extends Controller
{ 
    use Transformation;
    use MagasinTrait;

    
    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
    }

     /**
     * @Route("/liste-or-livrer", name="magasinListe_or_Livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
       


        $form = self::$validator->createBuilder(MagasinListeOrALivrerSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 

        $lesOrSelonCondition = $this->recupNumOrSelonCondition($criteria);

            $data = $this->magasinListOrLivrerModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);
        
            //enregistrer les critère de recherche dans la session
            $this->sessionService->set('magasin_liste_or_livrer_search_criteria', $criteria);

   
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($numeroOr);
                $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanning2($numeroOr);
                $data[$i]['nomPrenom'] = $this->magasinListOrLivrerModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
                
                if(!empty($datePlannig1)){
                    $data[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
                } else if(!empty($datePlannig2)){
                    $data[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
                } else {
                    $data[$i]['datePlanning'] = '';
                }
                // $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
                // if( !empty($dit)){
                //     $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                //     $data[$i]['niveauUrgence'] = $dit[0]['description'];
                // } else {
                //     break;
                // }
            }

            usort($data, function ($a, $b) {
                $dateA = isset($a['datePlanning']) ? $a['datePlanning'] : null;
                $dateB = isset($b['datePlanning']) ? $b['datePlanning'] : null;
                
                if ($dateA === $dateB) {
                    return 0;
                }
            
                // Place les `null` en bas
                if ($dateA === null) {
                    return 1;
                }
                if ($dateB === null) {
                    return -1;
                }
            
                // Comparer les dates pour les autres entrées
                return strtotime($dateA) - strtotime($dateB);
            });

        self::$twig->display('magasin/listOrLivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/magasin-list-or-livrer-export-excel", name="magasin_list_or_livrer")
     *
     * @return void
     */
    public function exportExcel()
    {
        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('magasin_liste_or_livrer_search_criteria', []);
        $lesOrSelonCondition = $this->recupNumOrSelonCondition($criteria);
        $entities = $this->magasinListOrLivrerModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);
       
        for ($i=0; $i < count($entities) ; $i++) { 
            $numeroOr = $entities[$i]['numeroor'];
            $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($numeroOr);
            $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanning2($numeroOr);
            $entities[$i]['nomPrenom'] = $this->magasinListOrLivrerModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
            
            if(!empty($datePlannig1)){
                $entities[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
            } else if(!empty($datePlannig2)){
                $entities[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
            } else {
                $entities[$i]['datePlanning'] = '';
            }
            // $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            // if( !empty($dit)){
            //     $entities[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
            //     $entities[$i]['niveauUrgence'] = $dit[0]['description'];
            // } else {
            //     break;
            // }
        }

        usort($entities, function ($a, $b) {
            $dateA = isset($a['datePlanning']) ? $a['datePlanning'] : null;
            $dateB = isset($b['datePlanning']) ? $b['datePlanning'] : null;
            
            if ($dateA === $dateB) {
                return 0;
            }
        
            // Place les `null` en bas
            if ($dateA === null) {
                return 1;
            }
            if ($dateB === null) {
                return -1;
            }
        
            // Comparer les dates pour les autres entrées
            return strtotime($dateA) - strtotime($dateB);
        });



    // Convertir les entités en tableau de données
    $data = [];
    $data[] = ['N° DIT', 'N° Or', "Date planning", "Date Or", "Agences", "Services", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'Qté à livr', 'Utilisateur']; 
    foreach ($entities as $entity) {
        
        $data[] = [
            $entity['referencedit'],
            $entity['numeroor'],
            $entity['datePlanning'],
            $entity['datecreation'],
            $entity['agence'],
            $entity['service'],
            $entity['numinterv'],
            $entity['numeroligne'],
            $entity['constructeur'],
            $entity['referencepiece'],
            $entity['designationi'],
            $entity['quantitedemander'],
            $entity['qtealivrer'],
            $entity['nomPrenom']
        ];
    }

         $this->excelService->createSpreadsheet($data);

    }



}