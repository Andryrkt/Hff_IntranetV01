<?php

use App\CustomSQLLogger;
use Doctrine\ORM\Tools\Setup;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
// Chemin vers les entités
$paths = array(dirname(__DIR__). "/src/Entity");
// Mode de développement
$isDevMode = true;

// Configuration de la base de données
$dbParams = array(
    'driver'   => 'pdo_sqlsrv',
    'host'     => $_ENV["DB_HOST"], 
    'port'     => '1433',
    'user'     => $_ENV["DB_USERNAME"] ,
    'password' => $_ENV["DB_PASSWORD"],
    'dbname'   => $_ENV["DB_NAME"],
);

// Configuration du lecteur d'annotations
$annotationReader = new AnnotationReader();
$driver = new AnnotationDriver($annotationReader, $paths);


//Création de la configuration Doctrine
$config = Setup::createConfiguration($isDevMode);
$config->setMetadataDriverImpl($driver);

//Ajout du logger SQL personnalisé
//$config->setSQLLogger(new CustomSQLLogger());


//Création de l'EntityManager
$entityManager = EntityManager::create($dbParams, $config);

// Configurez Doctrine pour utiliser l'AnnotationReader standard
// $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
// $annotationReader = new AnnotationReader();

// // Créez le driver des annotations
// $driver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver($annotationReader, $paths);
// $config->setMetadataDriverImpl($driver);

// // Création de l'EntityManager
// $entityManager = EntityManager::create($dbParams, $config);


// Créer une instance de SimpleManagerRegistry
$managerRegistry = new SimpleManagerRegistry($entityManager);