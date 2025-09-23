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

// Vérifier si les proxies existent, sinon les régénérer
$proxyFiles = glob($proxyDir . '/__CG__*.php');
if (empty($proxyFiles)) {
    // Les proxies seront régénérés automatiquement par Doctrine
    // lors de la première utilisation de l'EntityManager
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


// Configuration DB - Utilisation du DSN ODBC directement
// Configuration DB
$dbParams = [
    'driver'   => 'pdo_sqlsrv',
    'host'     => $_ENV["DB_HOST"],
    'port'     => '1433',
    'user'     => $_ENV["DB_USER"],
    'password' => $_ENV["DB_PASSWORD"],
    'dbname'   => $_ENV["DB_NAME"],
    'options'  => [],
];

// EntityManager
try {
    $entityManager = EntityManager::create($dbParams, $config);
} catch (\Exception $e) {
    // Fallback pour les versions récentes de Doctrine
    $connection = \Doctrine\DBAL\DriverManager::getConnection($dbParams);
    $entityManager = new EntityManager($connection, $config);
}
