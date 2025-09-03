<?php

/**
 * Script de lancement des tests pour le module DOM
 */

require_once '../vendor/autoload.php';

use PHPUnit\TextUI\Command;

echo "=== Lancement des tests DOM ===\n\n";

// Configuration des tests
$options = [
    'configuration' => __DIR__ . '/phpunit.xml',
    'testsuite' => 'DOM All Tests',
    'colors' => 'always',
    'verbose' => true
];

try {
    // Lancement des tests
    $command = new Command();
    $result = $command->run($options, false);

    if ($result === 0) {
        echo "\n✅ Tous les tests sont passés avec succès !\n";
    } else {
        echo "\n❌ Certains tests ont échoué.\n";
    }

    exit($result);
} catch (Exception $e) {
    echo "\n❌ Erreur lors de l'exécution des tests: " . $e->getMessage() . "\n";
    exit(1);
}
