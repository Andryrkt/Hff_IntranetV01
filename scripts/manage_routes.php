<?php

/**
 * Script de gestion des routes essentielles
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\RouteLoaderService;

echo "=== GESTION DES ROUTES ESSENTIELLES ===\n\n";

try {
    $routeLoader = new RouteLoaderService();
    $routes = $routeLoader->getLoadedRoutes();

    echo "✅ " . count($routes) . " routes essentielles chargées :\n\n";

    // Grouper les routes par catégorie
    $categories = [
        'Critiques (chargées au démarrage)' => ['login', 'profil_acceuil', 'logout', 'auth_deconnexion']
    ];

    foreach ($categories as $category => $routeNames) {
        echo "📁 $category :\n";
        foreach ($routes as $route) {
            if (in_array($route['name'], $routeNames)) {
                echo "   • {$route['name']} → {$route['path']}\n";
            }
        }
        echo "\n";
    }

    echo "=== INSTRUCTIONS ===\n";
    echo "• Pour ajouter une route : Éditez config/routes/essential_routes.yaml\n";
    echo "• Pour voir toutes les routes : php scripts/manage_routes.php --list\n";
    echo "• Pour tester les performances : php test_simple_perf.php\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Vérifiez que le fichier config/routes/essential_routes.yaml existe.\n";
}
