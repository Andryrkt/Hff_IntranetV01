<?php

// Test simple du système de routage
echo "=== Test Simple du Système de Routage ===\n";

// Charger le bootstrap DI
$services = require 'config/bootstrap_di.php';

echo "1. Vérification des services de routage...\n";
if (isset($services['routeCollection']) && isset($services['urlGenerator'])) {
    echo "✅ Collection de routes disponible\n";
    echo "✅ UrlGenerator disponible\n";
} else {
    echo "❌ Services de routage manquants\n";
    exit(1);
}

// Vérifier que la route profil_acceuil existe
$routeCollection = $services['routeCollection'];
$urlGenerator = $services['urlGenerator'];

echo "\n2. Vérification des routes chargées...\n";
$routes = $routeCollection->all();
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

// Tester la génération d'URL
echo "\n3. Test de génération d'URL...\n";
try {
    $url = $urlGenerator->generate('profil_acceuil');
    echo "✅ URL générée avec succès : $url\n";
} catch (\Exception $e) {
    echo "❌ Erreur lors de la génération d'URL : " . $e->getMessage() . "\n";
}

echo "\n=== Fin du test ===\n";
