<?php

namespace App\Controller\security;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SiginSecurity extends Controller
{
    /**
     * @Route("/", name="security_signin")
     */
    public function affichageSingnin()
    {
        self::$twig->display('signin.html.twig');
    }
}
