<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    private $menuService;

    public function __construct()
    {
        parent::__construct();
        $this->menuService = new MenuService(self::$em);
    }

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->logUserVisit('profil_acceuil'); // historisation du page visité par l'utilisateur

        $menuItems = $this->menuService->getMenuStructure();

        self::$twig->display(
            'main/accueil.html.twig',
            [
                'menuItems' => $menuItems,
            ]
        );
    }
}
