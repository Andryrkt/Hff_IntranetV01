<?php
 namespace App\Controller\inventaire;
 
use DateTime;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use App\Model\inventaire\DetailInventaireModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\inventaire\DetailInventaireSearch;
use App\Form\inventaire\detailInventaireSearchType;

class DetailInventaireController extends Controller{
    use FormatageTrait;
    use Transformation;
    private DetailInventaireModel $detailInventaireModel;
    private DetailInventaireSearch $DetailInventaireSearch;
    public function __construct()
    {
        parent::__construct();
        $this->detailInventaireModel = new DetailInventaireModel;
        $this->DetailInventaireSearch = new DetailInventaireSearch;
        
    }
   /**
     * @Route("/inventaire_detail", name = "liste_detail_inventaire")
     * 
     * @return void
     */
    public function listeDetailInventaire(Request $request){
         //verification si user connecter
         $this->verifierSessionUtilisateur();
         $form = self::$validator->createBuilder(
            detailInventaireSearchType::class,
            $this->DetailInventaireSearch,[
                'method'=>'GET'
            ]
         )->getForm();
         $form->handleRequest($request);
         $criteria = $this->DetailInventaireSearch;
         self::$twig->display('inventaire/detailInventaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}