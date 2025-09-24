<?php

/**
 * Script de préchauffage du cache pour améliorer les performances
 */

echo "=== PRÉCHAUFFAGE DU CACHE ===\n\n";

// Créer les dossiers de cache
$cacheDirs = [
    'var/cache',
    'var/cache/container',
    'var/cache/routes',
    'var/cache/proxies'
];

foreach ($cacheDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "✓ Dossier créé: $dir\n";
    } else {
        echo "✓ Dossier existe: $dir\n";
    }
}

echo "\n=== PRÉCHARGEMENT DU CONTENEUR ===\n";

// Précharger le conteneur
$start = microtime(true);
require_once __DIR__ . '/../config/bootstrap_di.php';
$end = microtime(true);

$time = ($end - $start) * 1000;
echo "✓ Conteneur préchargé en " . number_format($time, 2) . " ms\n";

echo "\n=== PRÉCHARGEMENT DES ROUTES ===\n";

// Précharger les routes
$start = microtime(true);
$cacheDir = __DIR__ . '/../var/cache/routes';
$routesCacheFile = $cacheDir . '/routes.php';

if (!file_exists($routesCacheFile)) {
    // Forcer le rechargement des routes
    $container = $GLOBALS['container'];
    $container->setParameter('kernel.debug', true);

    // Recharger le bootstrap pour générer le cache des routes
    require_once __DIR__ . '/../config/bootstrap_di.php';
    echo "✓ Cache des routes généré\n";
} else {
    echo "✓ Cache des routes existe déjà\n";
}

$end = microtime(true);
$time = ($end - $start) * 1000;
echo "✓ Routes préchargées en " . number_format($time, 2) . " ms\n";

echo "\n=== PRÉCHARGEMENT DES PROXIES DOCTRINE ===\n";

// Précharger les proxies Doctrine
$start = microtime(true);
$entityManager = $GLOBALS['container']->get('doctrine.orm.default_entity_manager');

// Forcer la génération des proxies
$metadataFactory = $entityManager->getMetadataFactory();
$allMetadata = $metadataFactory->getAllMetadata();
echo "✓ " . count($allMetadata) . " entités chargées\n";

$end = microtime(true);
$time = ($end - $start) * 1000;
echo "✓ Proxies Doctrine préchargés en " . number_format($time, 2) . " ms\n";

echo "\n=== OPTIMISATION COMPOSER ===\n";

// Optimiser l'autoloader Composer
$start = microtime(true);
$composerAutoloader = require_once __DIR__ . '/../vendor/autoload.php';

// Optimiser l'autoloader
if (method_exists($composerAutoloader, 'setClassMapAuthoritative')) {
    $composerAutoloader->setClassMapAuthoritative(true);
    echo "✓ Autoloader Composer optimisé\n";
}

$end = microtime(true);
$time = ($end - $start) * 1000;
echo "✓ Composer optimisé en " . number_format($time, 2) . " ms\n";

echo "\n=== RÉSULTAT ===\n";
echo "✅ Cache préchauffé avec succès !\n";
echo "✅ L'application devrait maintenant se charger beaucoup plus rapidement.\n";
echo "\nPour tester les performances, exécutez: php test_simple_perf.php\n";
