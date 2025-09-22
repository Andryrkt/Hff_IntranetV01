<?php

/**
 * Script pour s'assurer que les proxies Doctrine sont toujours présents
 * Ce script doit être exécuté automatiquement au démarrage de l'application
 */

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/dotenv.php';

// Charger l'EntityManager
$entityManager = require_once __DIR__ . "/../doctrineBootstrap.php";

// Créer le dossier proxies s'il n'existe pas
$proxyDir = __DIR__ . '/../var/cache/proxies';
if (!is_dir($proxyDir)) {
    mkdir($proxyDir, 0755, true);
}

// Vérifier si les proxies existent
$proxyFiles = glob($proxyDir . '/__CG__*.php');
if (empty($proxyFiles)) {
    // Forcer la génération des proxies
    $proxyFactory = $entityManager->getProxyFactory();
    $proxyFactory->generateProxyClasses($entityManager->getMetadataFactory()->getAllMetadata());

    echo "Proxies Doctrine régénérés automatiquement.\n";
}
