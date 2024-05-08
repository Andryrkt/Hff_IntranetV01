<?php

namespace App\Controller\security;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SiginSecurity extends Controller
{
    /**
     * @Route("/Hffintranet/", name="security_signin")
     */
    public function affichageSingnin()
    {
        $this->twig->display('signin.html.twig');
    }
}
