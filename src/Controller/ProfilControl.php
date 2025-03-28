<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;


class ProfilControl extends Controller
{
    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->logUserVisit('profil_acceuil'); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'main/accueil.html.twig'
        );
    }
}
