<?php

namespace App\Controller;

use App\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProfilControl extends AbstractController
{

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        $this->render('main/accueil.html.twig');
    }
}

