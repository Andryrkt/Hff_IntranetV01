<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class PointageRelanceController extends Controller
{
    /**
     * @Route("/pointage-relance", name="devis_magasin_relance_client")
     */
    public function index()
    {

        return $this->render('magasin/devis/pointage_relance/index.html.twig', [
            'controller_name' => 'PointageRelanceController',
        ]);
    }
}
