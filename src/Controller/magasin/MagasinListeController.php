<?php


namespace App\Controller\magasin;

ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Controller\Traits\MagasinTrait;
use App\Controller\Traits\Transformation;
use App\Entity\DemandeIntervention;
use App\Form\MagasinListOrSearchType;
use App\Form\MagasinSearchType;
use App\Model\magasin\MagasinListeOrModel;
use App\Model\magasin\MagasinModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MagasinListeController extends Controller
{ 
    use Transformation;
    use MagasinTrait;

    

    private $magasinModel;
    private $magasinListOrModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinModel = new MagasinModel();
        $this->magasinListOrModel = new MagasinListeOrModel();
    }
    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $empty = false;

        $form = self::$validator->createBuilder(MagasinSearchType::class, null, [
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


        $numOrValideString = $this->orEnString($criteria);

            $data = $this->magasinModel->recupereListeMaterielValider($numOrValideString, $criteria);
            
            //ajouter le numero dit dans data
            for ($i=0; $i < count($data) ; $i++) { 
                $numeroOr = $data[$i]['numeroor'];
                $dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
                if( !empty($dit)){
                    $data[$i]['numDit'] = $dit[0]['numeroDemandeIntervention'];
                    $data[$i]['niveauUrgence'] = $dit[0]['description'];
                } else {
                    $empty = true;
                    break;
                }
            }
       

        
        if(empty($data)  ){
            $empty = true;
        }
       
        self::$twig->display('magasin/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'empty' => $empty,
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/liste-or", name="liste_or")
     *
     * @return void
     */
    public function listOr(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $empty = false;


        $monday = $this->firstDateOfWeek();

        $dateDay = new \DateTime($this->getDatesystem());

        $form = self::$validator->createBuilder(MagasinListOrSearchType::class, ['monday' => $monday, 'dateDay' => $dateDay], [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
           $criteria = ['dateDebut' => $monday];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 
        $data = $this->magasinListOrModel->recupereListeMaterielValider($criteria);
   
        if(empty($data)  ){
            $empty = true;
        }
       
        self::$twig->display('magasin/listOr.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data,
            'empty' => $empty,
            'form' => $form->createView()
        ]);
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