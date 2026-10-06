<?php

/**
 * Script de test rapide pour DevisMagasinValidationVpOrchestrator
 * 
 * Ce script permet de tester rapidement les fonctionnalités de base
 * sans avoir besoin de PHPUnit complet.
 */

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use App\Service\magasin\devis\Validator\DevisMagasinValidationVpOrchestrator;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use Symfony\Component\Form\FormInterface;

echo "🧪 Test rapide de DevisMagasinValidationVpOrchestrator\n";
echo "====================================================\n\n";

// Service d'historique factice (neutralise notifications et redirections)
class FakeHistoriqueOperationDevisMagasinService extends HistoriqueOperationDevisMagasinService
{
    public function __construct() {}
    public function sendNotificationSoumission(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
    public function sendNotificationValidation(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
    public function sendNotificationModification(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
    public function sendNotificationSuppression(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
    public function sendNotificationCreation(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
    public function sendNotificationCloture(string $message, string $numeroDocument, string $routeName, bool $success = false) {}
}

// Test 1: Instanciation
echo "1. Test d'instanciation...\n";
try {
    $orchestrator = new DevisMagasinValidationVpOrchestrator(
        new FakeHistoriqueOperationDevisMagasinService(),
        'DEV123456'
    );
    echo "✅ Instanciation réussie\n";
} catch (Exception $e) {
    echo "❌ Erreur d'instanciation: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Méthode checkMissingIdentifier
echo "\n2. Test de checkMissingIdentifier...\n";
$testCases = [
    ['input' => 'DEV123456', 'expected' => 'bool'],
    ['input' => null, 'expected' => 'bool'],
    ['input' => '', 'expected' => 'bool'],
    ['input' => '   ', 'expected' => 'bool'],
];

foreach ($testCases as $testCase) {
    try {
        $result = $orchestrator->checkMissingIdentifier($testCase['input']);
        $type = gettype($result);
        if ($type === $testCase['expected']) {
            echo "✅ checkMissingIdentifier('{$testCase['input']}') -> {$type}\n";
        } else {
            echo "❌ checkMissingIdentifier('{$testCase['input']}') -> {$type} (attendu: {$testCase['expected']})\n";
        }
    } catch (Exception $e) {
        echo "❌ Erreur avec '{$testCase['input']}': " . $e->getMessage() . "\n";
    }
}

// Test 3: validateBeforeVpSubmission (ignoré car nécessite un repository Doctrine réel)
echo "\n3. Test de validateBeforeVpSubmission... (ignoré)\n";

// Test 4: Performance (ignoré car dépend du repository)
echo "\n4. Test de performance... (ignoré)\n";

// Test 5: Méthodes de statut (ignorées car nécessitent un repository Doctrine)
echo "\n5. Test des méthodes de statut... (ignoré)\n";

// Test 6: Vérification des propriétés privées
echo "\n6. Vérification des propriétés privées...\n";
$reflection = new ReflectionClass($orchestrator);
$privateProperties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

foreach ($privateProperties as $property) {
    $property->setAccessible(true);
    $value = $property->getValue($orchestrator);
    $type = gettype($value);
    echo "✅ {$property->getName()} -> {$type}\n";
}

echo "\n🎉 Tests rapides terminés !\n";
echo "==========================\n";
echo "Pour des tests complets, utilisez : php run_tests.php\n";
echo "Ou exécutez PHPUnit directement : vendor/bin/phpunit --configuration=phpunit.xml\n";
