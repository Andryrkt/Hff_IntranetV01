<?php

namespace App\Controller\accueil;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function getMenuService(): MenuService
    {
        return $this->getContainer()->get('menuService');
    }

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        $menu = [];

        // Vérifier si l'utilisateur est connecté
        if ($this->isUserConnected()) {
            try {
                // Utiliser le MenuService pour récupérer le menu
                $menuService = $this->getMenuService();
                $menu = $menuService->getMenuStructure();
            } catch (Exception $e) {
                // En cas d'erreur, on continue sans menu
                error_log("Erreur MenuService: " . $e->getMessage());
            }
        } else {
            // Si l'utilisateur n'est pas connecté, on utilise le menu par défaut
            $this->redirectToRoute('security_signin');
        }

        $userInfo = $this->getSessionService()->get('user_info');

        return $this->render('main/accueil.html.twig', [
            'menuItems' => $menu,
            'hasDAP'    => in_array(Application::ID_DAP, $userInfo["applications"] ?? []),
        ]);
    }
}
