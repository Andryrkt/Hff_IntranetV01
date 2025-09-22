<?php

/**
 * Script de maintenance pour vérifier et régénérer les proxies Doctrine
 * Ce script peut être exécuté via cron ou manuellement
 */

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/dotenv.php';

// Charger l'EntityManager
$entityManager = require_once __DIR__ . "/../doctrineBootstrap.php";

// Créer le dossier proxies s'il n'existe pas
$proxyDir = __DIR__ . '/../var/cache/proxies';
if (!is_dir($proxyDir)) {
    mkdir($proxyDir, 0755, true);
    echo "Dossier proxies créé.\n";
}

// Vérifier si les proxies existent
$proxyFiles = glob($proxyDir . '/__CG__*.php');
if (empty($proxyFiles)) {
    echo "Aucun proxy trouvé. Régénération en cours...\n";

    // Forcer la génération des proxies
    $proxyFactory = $entityManager->getProxyFactory();
    $proxyFactory->generateProxyClasses($entityManager->getMetadataFactory()->getAllMetadata());

    echo "Proxies Doctrine régénérés avec succès !\n";
} else {
    echo "Proxies Doctrine OK (" . count($proxyFiles) . " fichiers trouvés).\n";
}
