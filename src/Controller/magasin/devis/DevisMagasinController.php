<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Form\magasin\devis\DevisMagasinType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinController extends Controller
{
    /**
     * @Route("/soumission-devis-magasin", name="devis_magasion_soumission")
     */
    public function soumission()
    {
        $form = self::$validator->createBuilder(DevisMagasinType::class)->getForm();
        self::$twig->display('magasin/devis/soumission.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
