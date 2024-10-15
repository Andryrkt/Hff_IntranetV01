<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DwDemandeIntervention;
use App\Form\dw\DossierInterventionAtelierSearchType;
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
        
        $form = self::$validator->createBuilder(DossierInterventionAtelierSearchType::class)->getForm();

        $dwDits = self::$em->getRepository(DwDemandeIntervention::class)->findAll();

        self::$twig->display('dw/dossierInterventionAtelier.html.twig', [
            'form' => $form->createView(),
            'dwDits' => $dwDits
        ]);
    }
}