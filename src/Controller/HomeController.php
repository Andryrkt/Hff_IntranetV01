<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use App\Utils\PerfLogger;
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

        $perfLogger = PerfLogger::getInstance();
        $perfLogger->log('Debut de la page d\'accueil', 'HomeController');

        // Vérifier si l'utilisateur est connecté
        if ($this->isUserConnected()) {
            $perfLogger->log("if (\$this->isUserConnected()) {", 'HomeController');
            try {
                // Utiliser le MenuService pour récupérer le menu
                $menuService = $this->getMenuService();
                $perfLogger->log("\$menuService = \$this->getMenuService();", 'HomeController');
                $menu = $menuService->getMenuStructure();
                $perfLogger->log("\$menu = \$menuService->getMenuStructure();", 'HomeController');
                $user = $this->getUser();
                $perfLogger->log("\$user = \$this->getUser();", 'HomeController');
            } catch (Exception $e) {
                $perfLogger->log("catch (Exception \$e)", 'HomeController');
                // En cas d'erreur, on continue sans menu
                error_log("Erreur MenuService: " . $e->getMessage());
            }
            $perfLogger->log("fin du try", 'HomeController');
        } else {
            $perfLogger->log("else", 'HomeController');
            // Si l'utilisateur n'est pas connecté, on utilise le menu par défaut
            $this->redirectToRoute('security_signin');
        }

        foreach ($user->getApplications() as $application) {
            $perfLogger->log("foreach (\$user->getApplications() as \$application)", 'HomeController');
            $appsByCode[$application->getCodeApp()] = true;
        }

        $perfLogger->log("fin de boucle", 'HomeController');

        return $this->render('main/accueil.html.twig', [
            'menuItems'  => $menu,
            'appsByCode' => $appsByCode,
        ]);
    }
}
