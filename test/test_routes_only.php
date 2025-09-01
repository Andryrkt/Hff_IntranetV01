<?php

// Test du chargement des routes uniquement
echo "=== Test du Chargement des Routes ===\n";

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;

echo "1. Chargement des routes depuis les contrôleurs...\n";

try {
    // Charger les routes
    $routeLoader = new AnnotationDirectoryLoader(
        new FileLocator(dirname(__DIR__) . '/src/Controller/'),
        new CustomAnnotationClassLoader(new AnnotationReader())
    );
    $controllerCollection = $routeLoader->load(dirname(__DIR__) . '/src/Controller/');

    // Charger les routes API
    $apiLoader = new AnnotationDirectoryLoader(
        new FileLocator(dirname(__DIR__) . '/src/Api/'),
        new CustomAnnotationClassLoader(new AnnotationReader())
    );
    $apiCollection = $apiLoader->load(dirname(__DIR__) . '/src/Api/');

    // Fusionner les collections de routes
    $collection = new RouteCollection();
    $collection->addCollection($controllerCollection);
    $collection->addCollection($apiCollection);

    echo "✅ Routes chargées avec succès\n";

    $routes = $collection->all();
    echo "📊 Nombre total de routes chargées : " . count($routes) . "\n";

    // Chercher spécifiquement la route profil_acceuil
    $profilRoute = null;
    foreach ($routes as $name => $route) {
        if ($name === 'profil_acceuil') {
            $profilRoute = $route;
            break;
        }
    }

    if ($profilRoute) {
        echo "✅ Route 'profil_acceuil' trouvée\n";
        echo "   - Path: " . $profilRoute->getPath() . "\n";
        echo "   - Methods: " . implode(', ', $profilRoute->getMethods()) . "\n";
    } else {
        echo "❌ Route 'profil_acceuil' NON trouvée\n";
        echo "Routes disponibles :\n";
        foreach (array_keys($routes) as $routeName) {
            echo "   - $routeName\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Erreur lors du chargement des routes : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n";
