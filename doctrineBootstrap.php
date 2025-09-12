<?php


use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/dotenv.php';

// Configuration
$paths = [__DIR__ . "/src/Entity"];
$isDevMode = false;

// Dossier des proxies
$proxyDir = str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/var/cache/proxies');
if (!file_exists($proxyDir)) {
    if (!mkdir($proxyDir, 0777, true)) {
        throw new \RuntimeException("Failed to create proxy directory");
    }
}

// Configuration Doctrine
$config = Setup::createAnnotationMetadataConfiguration(
    $paths,
    $isDevMode,
    $proxyDir,
    null,
    false
);

$config->setProxyNamespace('App\\Proxies');
$config->setAutoGenerateProxyClasses(false); // en mode dev true / mode prod false


// Configuration DB
$dbParams = [
    'driver'   => 'pdo_sqlsrv',
    'host'     => $_ENV["DB_HOST"],
    'port'     => '1433',
    'user'     => $_ENV["DB_USERNAME"],
    'password' => $_ENV["DB_PASSWORD"],
    'dbname'   => $_ENV["DB_NAME"],
    'options'  => [],
];

// EntityManager
try {
    $entityManager = EntityManager::create($dbParams, $config);
    return $entityManager;
} catch (\Exception $e) {
    // Fallback pour les versions récentes de Doctrine
    $connection = \Doctrine\DBAL\DriverManager::getConnection($dbParams);
    $entityManager = new EntityManager($connection, $config);
    return $entityManager;
}
