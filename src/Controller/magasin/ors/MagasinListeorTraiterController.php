<?php


namespace App\Controller\magasin\ors;

ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Model\magasin\MagasinModel;
use App\Controller\Traits\MagasinTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\Transformation;
use App\Model\magasin\MagasinListeOrModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;

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
        $agenceServiceUser = $this->agenceServiceIpsObjet();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        if($autoriser)
        {
            $agenceUser = null;
        } else {
            $agenceUser = $agenceServiceUser['agenceIps']->getCodeAgence() .'-'.$agenceServiceUser['agenceIps']->getLibelleAgence();
        }

        
        $form = self::$validator->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
            $criteria = [
                "agenceUser" => $agenceUser
            ];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 

        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, self::$em);
        
            $data = $this->magasinModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

            //enregistrer les critère de recherche dans la session
            $this->sessionService->set('magasin_liste_or_traiter_search_criteria', $criteria);
            
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $data[$i]['nomPrenom'] = $this->magasinModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
                $datePlannig1 = $this->magasinModel->recupDatePlanning1($numeroOr);
                $datePlannig2 = $this->magasinModel->recupDatePlanning2($numeroOr);
                if(!empty($datePlannig1)){
                    $data[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
                } else if(!empty($datePlannig2)){
                    $data[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
                } else {
                    $data[$i]['datePlanning'] = '';
                }
                $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
                if( !empty($dit)){
                    $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                    $data[$i]['niveauUrgence'] = $dit[0]['description'];
                } 
            }

        self::$twig->display('magasin/ors/listOrATraiter.html.twig', [
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
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, self::$em);
        $entities = $this->magasinModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

         //ajouter le numero dit dans data
         for ($i=0; $i < count($entities) ; $i++) { 
            $numeroOr = $entities[$i]['numeroor'];
            $datePlannig1 = $this->magasinModel->recupDatePlanning1($numeroOr);
            $datePlannig2 = $this->magasinModel->recupDatePlanning2($numeroOr);
            $entities[$i]['nomPrenom'] = $this->magasinModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
            if(!empty($datePlannig1)){
                $entities[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
            } else if(!empty($datePlannig2)){
                $entities[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
            } else {
                $entities[$i]['datePlanning'] = '';
            }
            $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            if( !empty($dit)){
                $entities[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                $entities[$i]['niveauUrgence'] = $dit[0]['description'];
            } else {
             
                break;
            }
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
    $data[] = ['N° DIT', 'N° Or', 'Date planning', "Date Or", "Agences", "Services", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'Qté à livr', 'Utilisateur']; 
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
            $entity['quantitelivree'],
            $entity['nomPrenom']
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