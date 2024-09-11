<?php


namespace App\Controller\magasin;

ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Model\magasin\MagasinModel;
use App\Controller\Traits\MagasinTrait;
use App\Controller\Traits\Transformation;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Model\magasin\MagasinListeOrModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MagasinListeOrTraiterController extends Controller
{ 
    use Transformation;
    use MagasinTrait;

    

    private $magasinModel;
    private $magasinListOrModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinModel = new MagasinListeOrATraiterModel;
        $this->magasinListOrModel = new MagasinListeOrModel();
    }



    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $form = self::$validator->createBuilder(MagasinListeOrATraiterSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
           
            // if ($criteria['niveauUrgence'] === null){
            //     $criteria = [];
            // }
        } 


        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria);

            $data = $this->magasinModel->recupereListeMaterielValider($criteria);

            //enregistrer les critère de recherche dans la session
            $this->sessionService->set('magasin_liste_or_traiter_search_criteria', $criteria);
            
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $datePlannig1 = $this->magasinModel->recupDatePlanning1($numeroOr);
                $datePlannig2 = $this->magasinModel->recupDatePlanning2($numeroOr);
                $data[$i]['nomPrenom'] = $this->magasinModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
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
                if ($a['datePlanning'] === $b['datePlanning']) {
                    return 0;
                }
                
                // Place les `null` en bas
                if ($a['datePlanning'] === null) {
                    return 1;
                }
                if ($b['datePlanning'] === null) {
                    return -1;
                }
            
                // Comparer les dates pour les autres entrées
                return strtotime($a['datePlanning']) - strtotime($b['datePlanning']);
            });
       
       
        self::$twig->display('magasin/listOrATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/magasin-list-or-traiter-export-excel", name="magasin_list_or_traiter")
     *
     * @return void
     */
    public function exportExcel()
    {
        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('magasin_liste_or_traiter_search_criteria', []);
        //$lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria);
        $entities = $this->magasinModel->recupereListeMaterielValider($criteria);
    // Convertir les entités en tableau de données
    $data = [];
    $data[] = ['N° DIT', 'N° Or', "Date Or", "Agences", "Services", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'Qté à livr']; 
    foreach ($entities as $entity) {
        $data[] = [
            $entity['referencedit'],
            $entity['numeroor'],
            $entity['datecreation'],
            $entity['agence'],
            $entity['service'],
            $entity['numinterv'],
            $entity['numeroligne'],
            $entity['constructeur'],
            $entity['referencepiece'],
            $entity['designationi'],
            $entity['quantitedemander'],
            $entity['quantitelivree'],
        ];
    }

         $this->excelService->createSpreadsheet($data);

    }

    /**
     * @Route("/designation-fetch/{designation}")
     *
     * @return void
     */
    public function autocompletionDesignation($designation)
    {
        if(!empty($designation)){
            $designations = $this->magasinModel->recupereAutocompletionDesignation($designation);
        } else {
            $designations = [];
        }

        header("Content-type:application/json");

        echo json_encode($designations);
    }


    /**
     * @Route("/refpiece-fetch/{refPiece}")
     *
     * @return void
     */
    public function autocompletionRefPiece($refPiece)
    {
        if(!empty($refPiece)){
            $refPieces = $this->magasinModel->recuperAutocompletionRefPiece($refPiece);
        } else {
            $refPieces = [];
        }


        header("Content-type:application/json");

        echo json_encode($refPieces);
    }


}