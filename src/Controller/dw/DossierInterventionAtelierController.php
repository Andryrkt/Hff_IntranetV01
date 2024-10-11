<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DossierInterventionAtelierController extends Controller
{
    /**
     * @Route("/dit-dossier-intervention-atelier", name="dit_dossier_intervention_atelier")
     *
     * @return void
     */
    public function dossierInterventionAtelier()
    {
        self::$twig->display('dit/dossierInterventionAtelier.html.twig');
    }
}