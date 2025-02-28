<?php

namespace App\Controller\bordereau;

use App\Controller\Controller;
use App\Model\bordereau\BordereauModel;
use App\Entity\Bordereau\BordereauSearch;
use App\Form\bordereau\BordereauSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class bordereauController extends Controller
{

    private BordereauModel $bordereauModel;
    private BordereauSearch $bordereauSearch;

    public function __construct()
    {
        parent::__construct();
        $this->bordereauModel = new BordereauModel();
        $this->bordereauSearch = new BordereauSearch();
    }

    /**
     * @Route("/bordereau", name = "bordereau_liste")
     * 
     * @return void
     */
    public function bordereauListe(Request $request){
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $form = self::$validator->createBuilder(
            BordereauSearchType::class,
            $this->bordereauSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->bordereauSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
            // dump($criteria);
          }

          //transformer l'objet zn tableau
          $criteriaTab = $criteria->toArray();
          $this->sessionService->set('bordereau_search_criteria',$criteriaTab);
          $data = [];
          if ($request->query->get('action') !== 'oui') {
            $bordereau = $this->bordereauModel->bordereauListe($criteria->getNuminv());
            // dd($bordereau);
          }
        self::$twig->display('bordereau/bordereau.html.twig', [
            'form' => $form->createView(),
            'data' => $bordereau
        ]);
    }
}
