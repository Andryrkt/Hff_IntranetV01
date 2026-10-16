<?php

namespace App\Doctrine;

require_once __DIR__ . '/../../config/dotenv.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

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

        // Cache persistant des définitions des entités (métadonnées) et des requêtes DQL compilées.
        // Sans APCu sur le serveur, Doctrine relisait les annotations des entités à chaque requête
        // (~1,5 s mesuré pour StatutDemande et les 7 entités liées, ~50 ms avec ce cache).
        // Le cache est vidé à chaque déploiement (var/cache) : après la modification d'une entité hors
        // déploiement, supprimer var/cache/doctrine.
        if (self::cacheActif()) {
            $dossierCache = __DIR__ . '/../../var/cache/doctrine';
            $config->setMetadataCache(new PhpFilesAdapter('metadonnees', 0, $dossierCache));
            $config->setQueryCache(new PhpFilesAdapter('requetes', 0, $dossierCache));
        }

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

    /**
     * Cache actif hors développement (APP_ENV différent de "dev") : en développement, les entités modifiées
     * sont relues sans avoir à vider le cache. DOCTRINE_CACHE=1 ou 0 force l'un ou l'autre.
     */
    private static function cacheActif(): bool
    {
        if (isset($_ENV['DOCTRINE_CACHE']) && $_ENV['DOCTRINE_CACHE'] !== '') {
            return $_ENV['DOCTRINE_CACHE'] === '1';
        }

        return ($_ENV['APP_ENV'] ?? 'prod') !== 'dev';
    }
}
