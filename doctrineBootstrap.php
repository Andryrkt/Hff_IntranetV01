<?php

use App\CustomSQLLogger;
use Doctrine\ORM\Tools\Setup;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
// Chemin vers les entités
$paths = array(dirname(__DIR__) . "/src/Entity");
// Mode de développement
$isDevMode = true;

// Configuration de la base de données
$dbParams = array(
    'driver'   => 'pdo_sqlsrv',
    'host'     => '192.168.0.28', 
    'port'     => '1433',
    'user'     => 'sa',
    'password' => 'Hff@sql2024',
    'dbname'   => 'HFF_INTRANET_TEST',
);

// Configuration du lecteur d'annotations
$annotationReader = new AnnotationReader();
$driver = new AnnotationDriver($annotationReader, $paths);

// Création de la configuration Doctrine
// $config = Setup::createConfiguration($isDevMode);
// $config->setMetadataDriverImpl($driver);


// Création de la configuration Doctrine
$config = Setup::createConfiguration($isDevMode);
$config->setMetadataDriverImpl($driver);

// Ajout du logger SQL personnalisé
// $config->setSQLLogger(new CustomSQLLogger());


// Création de l'EntityManager
$entityManager = EntityManager::create($dbParams, $config);

// Créer une instance de SimpleManagerRegistry
$managerRegistry = new SimpleManagerRegistry($entityManager);