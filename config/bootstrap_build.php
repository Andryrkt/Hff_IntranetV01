<?php

use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Illuminate\Pagination\Paginator;
use App\Doctrine\EntityManagerFactory;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require dirname(__DIR__) . '/vendor/autoload.php';

echo "🔨 BUILD MODE - Compilation pour PRODUCTION\n\n";

// Cache directory
$cacheDir = dirname(__DIR__) . '/var/cache';
@mkdir($cacheDir, 0777, true);

// ========================================
// CONTENEUR
// ========================================

// Container
$container = new ContainerBuilder();
$container->setParameter('kernel.project_dir', dirname(__DIR__));
$container->setParameter('kernel.cache_dir', $cacheDir);
$container->setParameter('kernel.debug', false);

// EntityManager
$entityManagerDef = new Definition(EntityManager::class);
$entityManagerDef->setFactory([EntityManagerFactory::class, 'createEntityManager']);
$entityManagerDef->setPublic(true);
$container->setDefinition('doctrine.orm.default_entity_manager', $entityManagerDef);

// ManagerRegistry (si tu utilises ton SimpleManagerRegistry)
$registryDef = new Definition(SimpleManagerRegistry::class, [
    $container->getDefinition('doctrine.orm.default_entity_manager')
]);
$registryDef->setPublic(true);
$container->setDefinition('doctrine', $registryDef);

// RequestStack
$requestStackDef = new Definition(RequestStack::class);
$requestStackDef->setPublic(true);
$container->setDefinition('request_stack', $requestStackDef);

// Charger les services YAML
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.yaml');
$loader->load('parameters.yaml');

// Pagination
Paginator::useBootstrap();

// Compiler et dump PHP natif
$container->compile();
$dumper = new PhpDumper($container);
file_put_contents($cacheDir . '/Container.php', $dumper->dump([
    'class' => 'AppContainer'
]));

echo "✅ Conteneur compilé : {$cacheDir}/Container.php\n";

// ========================================
// ROUTES
// ========================================

$routeCacheFile = $cacheDir . '/routes.php';
$cacheRoutes = new ConfigCache($routeCacheFile, false); // Forcer l'écriture

$collection = new RouteCollection();
$annotationReader = new AnnotationReader();

$dirs = [
    dirname(__DIR__) . '/src/Controller',
    dirname(__DIR__) . '/src/Api',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;

    $routeLoader = new AnnotationDirectoryLoader(
        new FileLocator($dir),
        new CustomAnnotationClassLoader($annotationReader)
    );

    $subCollection = $routeLoader->load($dir);
    $collection->addCollection($subCollection);

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $collection->addResource(new FileResource($file->getPathname()));
        }
    }
}

foreach ($collection as $route) {
    $route->setOption('case_sensitive', false);
}

$cacheRoutes->write(serialize($collection), $collection->getResources());

echo "✅ Routes mises en cache : {$routeCacheFile}\n";

// ========================================
// TWIG (préparation répertoire)
// ========================================

$twigCacheDir = $cacheDir . '/twig';
@mkdir($twigCacheDir, 0777, true);

// Supprimer le marqueur de compilation pour forcer la recompilation au prochain démarrage
$twigCompiledMarker = $twigCacheDir . '/.compiled';
if (file_exists($twigCompiledMarker)) unlink($twigCompiledMarker);

echo "✅ Twig : Répertoire cache préparé (compilation au premier démarrage)\n";

echo "\n🎉 BUILD TERMINÉ\n";
echo "💡 Les templates Twig seront compilés automatiquement au premier démarrage en PROD\n";
