<?php


namespace App\Controller\magasin;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Controller\Controller;
use App\Controller\Traits\MagasinTrait;
use App\Controller\Traits\Transformation;
use App\Model\magasin\MagasinListeOrEncoursModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\MagasinListOrEncoursSearchType;

class MagasinListeOrEncoursController extends Controller
{ 
    use Transformation;
    use MagasinTrait;

    
    private $magasinListOrEncoursModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrEncoursModel = new MagasinListeOrEncoursModel();
    }

     /**
     * @Route("/liste-or-encours", name="magasinListe_or_encours")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
        $form = self::$validator->createBuilder(MagasinListOrEncoursSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 

        $lesOrSelonCond = $this->recupNumOrSelonCond($criteria);

            $data = $this->magasinListOrEncoursModel->recupereListeMaterielValider($criteria, $lesOrSelonCond);
            
            //enregistrer les critère de recherche dans la session
            $this->sessionService->set('magasin_liste_or_encours_search_criteria', $criteria);

            //ajouter le numero dit dans data
            // for ($i=0; $i < count($data) ; $i++) { 
            //     $numeroOr = $data[$i]['numeroor'];
            //     $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            //     if( !empty($dit)){
            //         $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
            //         $data[$i]['niveauUrgence'] = $dit[0]['description'];
            //     } else {
            //         break;
            //     }
            // }


        self::$twig->display('magasin/listOrEncours.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/magasin-list-or-encours-export-excel", name="magasin_list_or_encours_excel")
     *
     * @return void
     */
    public function exportExcel()
    {
        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('magasin_liste_or_encours_search_criteria', []);
        $lesOrSelonCond = $this->recupNumOrSelonCond($criteria);
        $entities = $this->magasinListOrEncoursModel->recupereListeMaterielValider($criteria, $lesOrSelonCond);

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
            $entity['qtealivrer'],
        ];
    }

         $this->excelService->createSpreadsheet($data);

    }



}