<?php

/**
 * Script d'exécution des tests pour DevisMagasinValidationVpOrchestrator
 * 
 * Ce script permet d'exécuter facilement tous les tests et de générer des rapports.
 */

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\TextUI\Command;

echo "🧪 Exécution des tests pour DevisMagasinValidationVpOrchestrator\n";
echo "================================================================\n\n";

// Configuration des chemins
$projectRoot = realpath(__DIR__ . '/../../../../');
$testDir = __DIR__;
$phpunitConfig = $testDir . '/phpunit.xml';

// Vérification de l'existence de PHPUnit
if (!file_exists($projectRoot . '/vendor/bin/phpunit')) {
    echo "❌ PHPUnit n'est pas installé. Veuillez exécuter : composer install\n";
    exit(1);
}

// Vérification de la configuration
if (!file_exists($phpunitConfig)) {
    echo "❌ Fichier de configuration PHPUnit introuvable : {$phpunitConfig}\n";
    exit(1);
}

// Création du répertoire de couverture si nécessaire
$coverageDir = $testDir . '/coverage';
if (!is_dir($coverageDir)) {
    mkdir($coverageDir, 0755, true);
    echo "📁 Répertoire de couverture créé : {$coverageDir}\n";
}

// Création du répertoire de logs si nécessaire
$logDir = $testDir . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "📁 Répertoire de logs créé : {$logDir}\n";
}

// Fonction pour exécuter les tests
function runTests($phpunitPath, $configPath, $testDir)
{
    $command = sprintf(
        '%s --configuration=%s --testdox --colors=always --coverage-html=%s/coverage --coverage-text --coverage-clover=%s/coverage.xml --log-junit=%s/junit.xml',
        $phpunitPath,
        $configPath,
        $testDir,
        $testDir,
        $testDir
    );

    echo "🚀 Exécution de la commande : {$command}\n\n";

    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);

    return [$output, $returnCode];
}

// Exécution des tests
echo "⏳ Lancement des tests...\n\n";

list($output, $returnCode) = runTests(
    $projectRoot . '/vendor/bin/phpunit',
    $phpunitConfig,
    $testDir
);

// Affichage des résultats
echo implode("\n", $output) . "\n\n";

if ($returnCode === 0) {
    echo "✅ Tous les tests sont passés avec succès !\n";
    echo "📊 Rapport de couverture disponible dans : {$coverageDir}/index.html\n";
    echo "📋 Rapport JUnit disponible dans : {$testDir}/junit.xml\n";
} else {
    echo "❌ Certains tests ont échoué (code de retour : {$returnCode})\n";
    echo "📋 Consultez les logs ci-dessus pour plus de détails\n";
}

// Statistiques des fichiers de test
$testFiles = [
    'DevisMagasinValidationVpOrchestratorTest.php',
    'DevisMagasinValidationVpOrchestratorIntegrationTest.php',
    'DevisMagasinValidationVpOrchestratorEdgeCasesTest.php'
];

echo "\n📈 Statistiques des fichiers de test :\n";
echo "=====================================\n";

foreach ($testFiles as $testFile) {
    $filePath = $testDir . '/' . $testFile;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $testCount = substr_count($content, 'public function test');
        $lineCount = count(file($filePath));
        echo "📄 {$testFile} : {$testCount} tests, {$lineCount} lignes\n";
    } else {
        echo "❌ {$testFile} : Fichier introuvable\n";
    }
}

// Instructions pour l'utilisation
echo "\n📚 Instructions d'utilisation :\n";
echo "==============================\n";
echo "1. Exécuter tous les tests : php run_tests.php\n";
echo "2. Exécuter un test spécifique : vendor/bin/phpunit --configuration=phpunit.xml DevisMagasinValidationVpOrchestratorTest.php\n";
echo "3. Exécuter avec couverture : vendor/bin/phpunit --configuration=phpunit.xml --coverage-html=coverage\n";
echo "4. Exécuter en mode verbose : vendor/bin/phpunit --configuration=phpunit.xml --verbose\n";
echo "5. Exécuter un seul test : vendor/bin/phpunit --configuration=phpunit.xml --filter testConstructor\n";

echo "\n🔧 Configuration recommandée pour le développement :\n";
echo "==================================================\n";
echo "- Utilisez un IDE avec support PHPUnit (PhpStorm, VS Code)\n";
echo "- Configurez des breakpoints dans les tests pour le debugging\n";
echo "- Utilisez --stop-on-failure pour arrêter au premier échec\n";
echo "- Utilisez --filter pour exécuter des tests spécifiques\n";

exit($returnCode);
