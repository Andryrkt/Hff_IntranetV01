<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

/**
 * Contrôleur de la page d'accueil refactorisé pour utiliser l'injection de dépendances
 */
class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function getMenuService(): MenuService
    {
        return $this->getContainer()->get('App\Service\navigation\MenuService');
    }

    /**
     * @Route("/", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        $menu = [];
        $appsByCode = [];
        $user = null;

        // Vérifier si l'utilisateur est connecté
        if ($this->isUserConnected()) {
            try {
                // Utiliser le MenuService pour récupérer le menu
                $menuService = $this->getMenuService();
                $menu = $menuService->getMenuStructure();
                $user = $this->getUser();
            } catch (Exception $e) {
                // En cas d'erreur, on continue sans menu
                error_log("Erreur MenuService: " . $e->getMessage());
            }
        } else {
            // Si l'utilisateur n'est pas connecté, on utilise le menu par défaut
            $this->redirectToRoute('security_signin');
        }

        foreach ($user->getApplications() as $application) {
            $appsByCode[$application->getCodeApp()] = true;
        }

        return $this->render('main/accueil.html.twig', [
            'menuItems'  => $menu,
            'appsByCode' => $appsByCode,
        ]);
    }
}
