<?php

/**
 * Script de test de performance pour l'application
 */

// Démarrer la session avant tout output
session_start();

echo "=== TEST DE PERFORMANCE DE L'APPLICATION ===\n\n";

// Test 1: Temps de chargement du bootstrap
echo "1. Test du chargement du bootstrap...\n";
$start = microtime(true);

require_once __DIR__ . '/config/bootstrap_di.php';

$end = microtime(true);
$bootstrapTime = ($end - $start) * 1000;
echo "   Temps de chargement du bootstrap: " . number_format($bootstrapTime, 2) . " ms\n\n";

// Test 2: Temps de création du conteneur
echo "2. Test de création du conteneur...\n";
$start = microtime(true);

$services = require_once __DIR__ . '/config/bootstrap_di.php';
$container = $services['container'];

$end = microtime(true);
$containerTime = ($end - $start) * 1000;
echo "   Temps de création du conteneur: " . number_format($containerTime, 2) . " ms\n\n";

// Test 3: Temps de résolution des services principaux
echo "3. Test de résolution des services...\n";
$start = microtime(true);

$entityManager = $container->get('doctrine.orm.default_entity_manager');
$twig = $container->get('Twig\Environment');
$session = $container->get('App\Service\SessionManagerService');

$end = microtime(true);
$servicesTime = ($end - $start) * 1000;
echo "   Temps de résolution des services: " . number_format($servicesTime, 2) . " ms\n\n";

// Test 4: Temps total
$totalTime = $bootstrapTime + $containerTime + $servicesTime;
echo "4. TEMPS TOTAL: " . number_format($totalTime, 2) . " ms\n\n";

// Recommandations
echo "=== RECOMMANDATIONS ===\n";
if ($totalTime > 1000) {
    echo "❌ PERFORMANCE CRITIQUE: L'application met plus de 1 seconde à charger\n";
    echo "   - Activez le cache du conteneur\n";
    echo "   - Optimisez l'autoloading Composer\n";
    echo "   - Vérifiez les connexions base de données\n";
} elseif ($totalTime > 500) {
    echo "⚠️  PERFORMANCE MOYENNE: L'application met plus de 500ms à charger\n";
    echo "   - Considérez l'activation du cache\n";
} else {
    echo "✅ PERFORMANCE BONNE: L'application se charge rapidement\n";
}

echo "\n=== DÉTAILS ===\n";
echo "Bootstrap: " . number_format($bootstrapTime, 2) . " ms\n";
echo "Conteneur: " . number_format($containerTime, 2) . " ms\n";
echo "Services: " . number_format($servicesTime, 2) . " ms\n";
echo "Total: " . number_format($totalTime, 2) . " ms\n";
