<?php

namespace App\Controller;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class Authentification extends Controller
{
    /**
     * @Route("/", name="security_signin")
     */
    public function affichageSingnin()
    {
        self::$twig->display('signin.html.twig');
    }

    /**
     * @Route("/logout", name="auth_deconnexion")
     *
     * @return void
     */
    public function deconnexion()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->SessionDestroy();
    }
}
