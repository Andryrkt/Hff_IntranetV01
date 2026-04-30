<?php

namespace App\Doctrine;

require_once __DIR__ . '/../../config/dotenv.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    private static ?EntityManager $entityManager = null;

    public static function createEntityManager(): EntityManager
    {
        if (self::$entityManager !== null) {
            return self::$entityManager;
        }

        $paths = [__DIR__ . "/../../src/Entity"];
        $isDevMode = false;

        $proxyDir = __DIR__ . "/../../var/cache/proxies";
        if (!file_exists($proxyDir)) {
            mkdir($proxyDir, 0777, true);
        }

        $config = Setup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            $proxyDir,
            null,
            false
        );

        $config->setProxyNamespace('App\\Proxies');
        $config->setAutoGenerateProxyClasses(false);

        $dbParams = [
            'driver'   => 'pdo_sqlsrv',
            'host'     => $_ENV["DB_HOST"],
            'port'     => '1433',
            'user'     => $_ENV["DB_USERNAME"],
            'password' => $_ENV["DB_PASSWORD"],
            'dbname'   => $_ENV["DB_NAME"],
        ];

        self::$entityManager = EntityManager::create($dbParams, $config);

        return self::$entityManager;
    }
}
