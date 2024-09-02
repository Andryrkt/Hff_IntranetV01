<?php


namespace App\Controller\magasin;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '500M');


use App\Controller\Controller;
use App\Form\MagasinSearchType;
use App\Entity\DemandeIntervention;
use App\Controller\Traits\MagasinTrait;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;

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
       
        $empty = false;

        $form = self::$validator->createBuilder(MagasinSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 

        //$numOrValideString = $this->orEnString($criteria);

            $data = $this->magasinListOrLivrerModel->recupereListeMaterielValider($criteria);
            
            //enregistrer les critère de recherche dans la session
            $this->sessionService->set('magasin_liste_or_livrer_search_criteria', $criteria);

            //ajouter le numero dit dans data
            // for ($i=0; $i < count($data) ; $i++) { 
            //     $numeroOr = $data[$i]['numeroor'];
            //     $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            //     if( !empty($dit)){
            //         $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
            //         $data[$i]['niveauUrgence'] = $dit[0]['description'];
            //     } else {
            //         $empty = true;
            //         break;
            //     }
            // }

        
        if(empty($data)  ){
            $empty = true;
        }
       
        self::$twig->display('magasin/listOrLivrer.html.twig', [
            'data' => $data,
            'empty' => $empty,
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

        $entities = $this->magasinListOrLivrerModel->recupereListeMaterielValider($criteria);

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