<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/liste-devis-magasin", name="devis_magasion_liste")
     */
    public function listeDevisMagasin()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        self::$twig->display('magasin/devis/listeDevisMagasin.html.twig', []);
    }
}
