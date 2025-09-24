<?php

/**
 * Script pour lister toutes les routes disponibles
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\RouteLoaderService;

echo "=== TOUTES LES ROUTES DISPONIBLES ===\n\n";

try {
    $routeLoader = new RouteLoaderService();

    // Routes critiques (chargées au démarrage)
    $criticalRoutes = $routeLoader->getLoadedRoutes();
    echo "🚨 ROUTES CRITIQUES (chargées au démarrage) : " . count($criticalRoutes) . "\n";
    foreach ($criticalRoutes as $route) {
        echo "   • {$route['name']} → {$route['path']}\n";
    }
    echo "\n";

    // Toutes les routes disponibles
    $allRoutes = $routeLoader->getAllRoutes();
    echo "📋 TOUTES LES ROUTES DISPONIBLES : " . count($allRoutes) . "\n";

    $importantCount = 0;
    $adminCount = 0;

    foreach ($allRoutes as $route) {
        if (!in_array($route['name'], ['login', 'profil_acceuil', 'logout', 'auth_deconnexion'])) {
            if (strpos($route['path'], '/admin/') === 0) {
                $adminCount++;
            } else {
                $importantCount++;
            }
        }
    }

    echo "   - Routes importantes : $importantCount\n";
    echo "   - Routes d'administration : $adminCount\n";
    echo "   - Routes critiques : " . count($criticalRoutes) . "\n\n";

    echo "=== RECOMMANDATIONS ===\n";
    echo "• Seules les routes critiques sont chargées au démarrage\n";
    echo "• Les autres routes sont chargées à la demande\n";
    echo "• Cela améliore significativement les performances\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
