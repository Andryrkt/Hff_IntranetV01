<?php

namespace App\Controller\magasin\lcfng;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\lcfng\ListeCdeFrnNonGenererModel;

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
    public function index()
    {
        $data = $this->listeCdeFrnNonGenererModel->getListeCdeFrnNonGenerer();

        self::$twig->display('magasin/lcfng/listCdeFnrNonGenerer.html.twig', [
            'data' => $data,
        ]);
    }
}