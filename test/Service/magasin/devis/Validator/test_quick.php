<?php

/**
 * Script de test rapide pour DevisMagasinValidationVpOrchestrator
 * 
 * Ce script permet de tester rapidement les fonctionnalités de base
 * sans avoir besoin de PHPUnit complet.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

use App\Service\magasin\devis\Validator\DevisMagasinValidationVpOrchestrator;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\StatusRepositoryInterface;
use Symfony\Component\Form\FormInterface;

echo "🧪 Test rapide de DevisMagasinValidationVpOrchestrator\n";
echo "====================================================\n\n";

// Mock du service d'historique
$mockHistoriqueService = new class implements HistoriqueOperationDevisMagasinService {
    public function enregistrerOperation(string $operation, string $details = ''): bool
    {
        return true;
    }
};

// Test 1: Instanciation
echo "1. Test d'instanciation...\n";
try {
    $orchestrator = new DevisMagasinValidationVpOrchestrator(
        $mockHistoriqueService,
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

// Test 3: Méthode validateBeforeVpSubmission
echo "\n3. Test de validateBeforeVpSubmission...\n";
$mockRepository = new class implements DevisMagasinRepository {
    public function find($id)
    {
        return null;
    }
    public function findAll()
    {
        return [];
    }
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return [];
    }
    public function findOneBy(array $criteria)
    {
        return null;
    }
    public function getClassName()
    {
        return 'DevisMagasin';
    }
    public function count(array $criteria = [])
    {
        return 0;
    }
};

$testCases = [
    ['numeroDevis' => 'DEV123456', 'sumOfLines' => 5, 'sumOfMontant' => 1000.50],
    ['numeroDevis' => null, 'sumOfLines' => 0, 'sumOfMontant' => 0.0],
    ['numeroDevis' => 'DEV123456', 'sumOfLines' => -1, 'sumOfMontant' => -100.0],
    ['numeroDevis' => 'DEV123456', 'sumOfLines' => PHP_INT_MAX, 'sumOfMontant' => PHP_FLOAT_MAX],
];

foreach ($testCases as $testCase) {
    try {
        $result = $orchestrator->validateBeforeVpSubmission(
            $mockRepository,
            $testCase['numeroDevis'],
            $testCase['sumOfLines'],
            $testCase['sumOfMontant']
        );
        $type = gettype($result);
        echo "✅ validateBeforeVpSubmission('{$testCase['numeroDevis']}', {$testCase['sumOfLines']}, {$testCase['sumOfMontant']}) -> {$type}\n";
    } catch (Exception $e) {
        echo "❌ Erreur avec validateBeforeVpSubmission: " . $e->getMessage() . "\n";
    }
}

// Test 4: Performance
echo "\n4. Test de performance...\n";
$iterations = 1000;
$startTime = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $result = $orchestrator->validateBeforeVpSubmission(
        $mockRepository,
        'DEV123456',
        5,
        1000.50
    );
}

$endTime = microtime(true);
$executionTime = $endTime - $startTime;
$averageTime = $executionTime / $iterations;

echo "✅ {$iterations} validations en " . number_format($executionTime, 4) . " secondes\n";
echo "✅ Temps moyen par validation: " . number_format($averageTime * 1000, 2) . " ms\n";

if ($executionTime < 1.0) {
    echo "✅ Performance acceptable (< 1 seconde)\n";
} else {
    echo "⚠️  Performance lente (> 1 seconde)\n";
}

// Test 5: Méthodes de statut
echo "\n5. Test des méthodes de statut...\n";
$statusMethods = [
    'verifierStatutPrixValideAgenceEtSommeDeLignesAndAmountInchangée',
    'verificationStatutPrixModifierAgenceEtSommeDeLignesInchangéeEtMontantchange',
    'verificationStatutValideAEnvoyerAuclientEtSommeDeLignesChangeEtMontantChange',
    'verifieStatutAvalideChefAgence',
    'verifieStatutValideAEnvoyerAuclientEtSommeLignesInchange',
    'verifieStatutClotureAModifierEtSommeLignesIpsInferieurSommeLignesDevis'
];

foreach ($statusMethods as $method) {
    try {
        if (method_exists($orchestrator, $method)) {
            // Appel avec des paramètres appropriés selon la méthode
            if (strpos($method, 'AvalideChefAgence') !== false) {
                $result = $orchestrator->$method($mockRepository, 'DEV123456');
            } elseif (strpos($method, 'ClotureAModifier') !== false) {
                $result = $orchestrator->$method($mockRepository, 'DEV123456', 5);
            } else {
                $result = $orchestrator->$method($mockRepository, 'DEV123456', 5, 1000.50);
            }
            $type = gettype($result);
            echo "✅ {$method}() -> {$type}\n";
        } else {
            echo "❌ Méthode {$method} non trouvée\n";
        }
    } catch (Exception $e) {
        echo "❌ Erreur avec {$method}: " . $e->getMessage() . "\n";
    }
}

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
