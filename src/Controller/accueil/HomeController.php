<?php

namespace App\Controller\accueil;

use App\Controller\Controller;
use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        /** @var MenuService $menuService */
        $menuService = $this->getContainer()->get('menuService');

        return $this->render('main/accueil.html.twig', [
            'menuItems' => $menuService->getMenuStructure(),
        ]);
    }
}
