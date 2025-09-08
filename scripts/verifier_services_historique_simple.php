<?php

/**
 * Script de vérification simplifié des services HistoriqueOperation
 */

require_once __DIR__ . '/../vendor/autoload.php';

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
    $filePath = __DIR__ . "/../src/Service/historiqueOperation/{$serviceName}.php";

    try {
        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            $errors[] = "❌ Fichier {$serviceName}.php introuvable";
            continue;
        }

        // Lire le contenu du fichier
        $sourceCode = file_get_contents($filePath);

        // Vérifier la présence de l'import EntityManagerInterface
        if (strpos($sourceCode, 'use Doctrine\\ORM\\EntityManagerInterface;') === false) {
            $errors[] = "❌ {$serviceName} : import EntityManagerInterface manquant";
            continue;
        }

        // Vérifier le constructeur avec EntityManagerInterface
        if (!preg_match('/public function __construct\(EntityManagerInterface \$em\)/', $sourceCode)) {
            $errors[] = "❌ {$serviceName} : constructeur avec EntityManagerInterface manquant";
            continue;
        }

        // Vérifier l'appel parent::__construct($em, $typeDocumentId)
        // Accepter soit la valeur littérale soit une constante
        $pattern1 = '/parent::__construct\(\$em,\s*' . $expectedTypeDocumentId . '\)/';
        $pattern2 = '/parent::__construct\(\$em,\s*self::TYPE_DOCUMENT\)/';

        if (!preg_match($pattern1, $sourceCode) && !preg_match($pattern2, $sourceCode)) {
            $errors[] = "❌ {$serviceName} : appel parent::__construct(\$em, {$expectedTypeDocumentId}) ou parent::__construct(\$em, self::TYPE_DOCUMENT) incorrect";
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
