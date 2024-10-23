<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Model\magasin\cis\CisATraiterModel;
use Symfony\Component\Routing\Annotation\Route;

class CisATraiterController extends Controller
{
    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisATraiter()
    {
        $cisATraiterModel = new CisATraiterModel();
        $data = $cisATraiterModel->listOrATraiter();

        self::$twig->display('magasin/cis/listOrATraiter.html.twig', [
            'data' => $data
        ]);
    }
}