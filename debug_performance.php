<?php

/**
 * Script de debug pour identifier les causes de lenteur
 */

// Démarrer la session avant tout output
session_start();

echo "=== DEBUG DE PERFORMANCE ===\n\n";

// Test 1: Temps de chargement des fichiers de base
echo "1. Test du chargement des fichiers de base...\n";
$start = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

$end = microtime(true);
$autoloadTime = ($end - $start) * 1000;
echo "   Temps d'autoload: " . number_format($autoloadTime, 2) . " ms\n\n";

// Test 2: Temps de chargement du bootstrap sans services
echo "2. Test du chargement du bootstrap sans services...\n";
$start = microtime(true);

// Charger seulement la configuration de base
$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('kernel.project_dir', dirname(__DIR__));
$container->setParameter('kernel.cache_dir', dirname(__DIR__) . '/var/cache');
$container->setParameter('kernel.debug', true);

$end = microtime(true);
$bootstrapTime = ($end - $start) * 1000;
echo "   Temps de bootstrap de base: " . number_format($bootstrapTime, 2) . " ms\n\n";

// Test 3: Temps de chargement des services
echo "3. Test du chargement des services...\n";
$start = microtime(true);

// Charger les services un par un pour identifier le problème
$loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \Symfony\Component\Config\FileLocator(__DIR__ . '/config'));
$loader->load('services.yaml');

$end = microtime(true);
$servicesTime = ($end - $start) * 1000;
echo "   Temps de chargement des services: " . number_format($servicesTime, 2) . " ms\n\n";

// Test 4: Temps de compilation
echo "4. Test de compilation du conteneur...\n";
$start = microtime(true);

$container->compile();

$end = microtime(true);
$compileTime = ($end - $start) * 1000;
echo "   Temps de compilation: " . number_format($compileTime, 2) . " ms\n\n";

// Test 5: Temps total
$totalTime = $autoloadTime + $bootstrapTime + $servicesTime + $compileTime;
echo "5. TEMPS TOTAL: " . number_format($totalTime, 2) . " ms\n\n";

// Recommandations
echo "=== RECOMMANDATIONS ===\n";
if ($totalTime > 1000) {
    echo "❌ PERFORMANCE CRITIQUE: L'application met plus de 1 seconde à charger\n";
    if ($autoloadTime > 1000) {
        echo "   - Problème d'autoloading Composer\n";
    }
    if ($servicesTime > 1000) {
        echo "   - Problème de chargement des services\n";
    }
    if ($compileTime > 1000) {
        echo "   - Problème de compilation du conteneur\n";
    }
} elseif ($totalTime > 500) {
    echo "⚠️  PERFORMANCE MOYENNE: L'application met plus de 500ms à charger\n";
} else {
    echo "✅ PERFORMANCE BONNE: L'application se charge rapidement\n";
}

echo "\n=== DÉTAILS ===\n";
echo "Autoload: " . number_format($autoloadTime, 2) . " ms\n";
echo "Bootstrap: " . number_format($bootstrapTime, 2) . " ms\n";
echo "Services: " . number_format($servicesTime, 2) . " ms\n";
echo "Compilation: " . number_format($compileTime, 2) . " ms\n";
echo "Total: " . number_format($totalTime, 2) . " ms\n";
