<?php

namespace App\Controller\accueil;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        return $this->render('main/accueil.html.twig', [
            'menuItems' => $this->getMenuService()->getMenuStructure(),
        ]);
    }
}
