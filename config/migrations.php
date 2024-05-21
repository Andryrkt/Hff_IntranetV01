// config/migrations.php
<?php


use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Migrations\Configuration\Migration\ConfigurationFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

require_once 'vendor/autoload.php';

// Chemin vers les entités
$paths = array(__DIR__ . "/../src/Entity");
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

// Configuration de Doctrine ORM avec AnnotationReader
$config = Setup::createConfiguration($isDevMode);
$annotationReader = new AnnotationReader();
$driver = new AnnotationDriver($annotationReader, $paths);
$config->setMetadataDriverImpl($driver);

// Création de l'EntityManager
$entityManager = EntityManager::create($dbParams, $config);

// Charger la configuration des migrations
$migrationConfig = new ConfigurationFile(__DIR__ . '/doctrine-migrations.php');
$dependencyFactory = DependencyFactory::fromEntityManager($migrationConfig, new ExistingEntityManager($entityManager));

return $dependencyFactory;

