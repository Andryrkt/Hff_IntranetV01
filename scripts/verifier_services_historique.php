<?php

/**
 * Script de vérification des services HistoriqueOperation
 * 
 * Ce script vérifie que tous les services enfants de HistoriqueOperationService
 * implémentent correctement l'injection de dépendance EntityManagerInterface.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\EntityManagerInterface;

// Liste des services à vérifier
$services = [
    'HistoriqueOperationACService' => 13,
    'HistoriqueOperationBADMService' => 8,
    'HistoriqueOperationBCService' => 12,
    'HistoriqueOperationCASService' => 9,
    'HistoriqueOperationCDEFNRService' => 13,
    'HistoriqueOperationCDEService' => 10,
    'HistoriqueOperationDaBcService' => 2,
    'HistoriqueOperationDaFacBlService' => 12,
    'HistoriqueOperationDAService' => 6,
    'HistoriqueOperationDDPService' => 15,
    'HistoriqueOperationDEVService' => 11,
    'HistoriqueOperationDevisMagasinService' => 11,
    'HistoriqueOperationDITService' => 1,
    'HistoriqueOperationDOMService' => 7,
    'HistoriqueOperationFACService' => 3,
    'HistoriqueOperationMUTService' => 16,
    'HistoriqueOperationORService' => 2,
    'HistoriqueOperationRIService' => 4,
    'HistoriqueOperationTIKService' => 5,
    'HistoriqueOperationBLService' => 2,
];

echo "=== Vérification des services HistoriqueOperation ===\n\n";

$errors = [];
$success = [];

foreach ($services as $serviceName => $expectedTypeDocumentId) {
    $className = "App\\Service\\historiqueOperation\\{$serviceName}";

    try {
        // Vérifier que la classe existe
        if (!class_exists($className)) {
            $errors[] = "❌ Classe {$serviceName} introuvable";
            continue;
        }

        // Vérifier que la classe étend HistoriqueOperationService
        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf('App\\Service\\historiqueOperation\\HistoriqueOperationService')) {
            $errors[] = "❌ {$serviceName} n'étend pas HistoriqueOperationService";
            continue;
        }

        // Vérifier le constructeur
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            $errors[] = "❌ {$serviceName} n'a pas de constructeur";
            continue;
        }

        $parameters = $constructor->getParameters();
        if (count($parameters) !== 1) {
            $errors[] = "❌ {$serviceName} doit avoir exactement 1 paramètre dans son constructeur";
            continue;
        }

        $firstParam = $parameters[0];
        $paramType = $firstParam->getType();
        if (!$paramType || $paramType->getName() !== 'Doctrine\\ORM\\EntityManagerInterface') {
            $errors[] = "❌ {$serviceName} : le premier paramètre doit être de type EntityManagerInterface";
            continue;
        }

        // Vérifier que le constructeur appelle parent::__construct($em, $typeDocumentId)
        $sourceCode = file_get_contents($reflection->getFileName());
        if (!preg_match('/parent::__construct\(\$em,\s*' . $expectedTypeDocumentId . '\)/', $sourceCode)) {
            $errors[] = "❌ {$serviceName} : l'appel parent::__construct(\$em, {$expectedTypeDocumentId}) est incorrect";
            continue;
        }

        $success[] = "✅ {$serviceName} : Configuration correcte (Type Document ID: {$expectedTypeDocumentId})";
    } catch (Exception $e) {
        $errors[] = "❌ {$serviceName} : Erreur lors de la vérification - " . $e->getMessage();
    }
}

// Afficher les résultats
echo "RÉSULTATS :\n";
echo "===========\n\n";

if (!empty($success)) {
    echo "SERVICES CORRECTS :\n";
    foreach ($success as $message) {
        echo $message . "\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERREURS DÉTECTÉES :\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
    echo "\n";
}

$totalServices = count($services);
$correctServices = count($success);
$errorServices = count($errors);

echo "RÉSUMÉ :\n";
echo "========\n";
echo "Total des services : {$totalServices}\n";
echo "Services corrects : {$correctServices}\n";
echo "Services avec erreurs : {$errorServices}\n";

if ($errorServices === 0) {
    echo "\n🎉 Tous les services sont correctement configurés !\n";
    exit(0);
} else {
    echo "\n⚠️  {$errorServices} service(s) nécessitent des corrections.\n";
    exit(1);
}
