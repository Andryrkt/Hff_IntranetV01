<?php

namespace App\Controller\magasin\lcfng;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfng\ListeCdeFrnNonGenererModel;
use App\Form\magasin\lcfng\ListeCdeFrnNonGenererSearchType;
use Symfony\Component\HttpFoundation\Request;

class ListeCdeFrnNonGenererController extends Controller
{

    private ListeCdeFrnNonGenererModel $listeCdeFrnNonGenererModel;
    public function __construct()
    {
        parent::__construct();
        
        $this->listeCdeFrnNonGenererModel = new ListeCdeFrnNonGenererModel();
    }

    /**
     * @Route("/magasin/liste_cde_frs_non_generer", name="liste_Cde_Frn_Non_Generer")
     *
     * @return void
     */
    public function index(Request $request)
    {

        $form = self::$validator->createBuilder(ListeCdeFrnNonGenererSearchType::class, [], [
            'method' => 'GET'
        ])->getForm();

        // $form->handleRequest($request);
        // $criteria = [];
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $criteria = $form->getData();
        // }

        $data = $this->listeCdeFrnNonGenererModel->getListeCdeFrnNonGenerer();

        self::$twig->display('magasin/lcfng/listCdeFnrNonGenerer.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }
}