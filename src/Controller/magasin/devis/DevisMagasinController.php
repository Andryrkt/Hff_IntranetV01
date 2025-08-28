<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/soumission-devis-magasin", name="devis_magasion_soumission")
     */
    public function soumission()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //création du formulaire
        $form = self::$validator->createBuilder(DevisMagasinType::class)->getForm();

        self::$twig->display('magasin/devis/soumission.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
