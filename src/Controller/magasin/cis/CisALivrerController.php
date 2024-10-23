<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Model\magasin\cis\CisALivrerModel;
use Symfony\Component\Routing\Annotation\Route;

class CisALivrerController extends Controller
{
    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer()
    {
        $cisATraiterModel = new CisALivrerModel();
        $data = $cisATraiterModel->listOrALivrer();

        self::$twig->display('magasin/cis/listALivrer.html.twig', [
            'data' => $data
        ]);
    }
}