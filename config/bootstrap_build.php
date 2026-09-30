<?php

use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Illuminate\Pagination\Paginator;
use App\Doctrine\EntityManagerFactory;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use App\Service\navigation\MenuService;
use App\Service\security\SecurityService;
use App\Service\UserData\UserDataService;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Generator\Dumper\CompiledUrlGeneratorDumper;

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

// =============================
// EntityManager
// =============================
$entityManagerDef = new Definition(EntityManager::class);
$entityManagerDef->setFactory([EntityManagerFactory::class, 'createEntityManager']);
$entityManagerDef->setPublic(true);

$container->setDefinition('doctrine.orm.default_entity_manager', $entityManagerDef);

// =============================
// ManagerRegistry
// =============================
$registryDef = new Definition(SimpleManagerRegistry::class, [
    new Reference('doctrine.orm.default_entity_manager')
]);
$registryDef->setPublic(true);

$container->setDefinition('doctrine', $registryDef);

// =============================
// 🔥 SESSION (déclaré comme service)
// =============================
$container->register('session.storage', NativeSessionStorage::class);

$container->register('session', Session::class)
    ->setArguments([
        new Reference('session.storage')
    ])
    ->setPublic(true);

// =============================
// 🔥 Cache SECURITY (déclaré comme service)
// =============================
$container->register('cache.security', FilesystemTagAwareAdapter::class)
    ->setArguments([
        'security',
        0,
        dirname(__DIR__) . '/var/cache/pools'
    ])
    ->setPublic(true);

// =============================
// 🔥 Cache MENU (déclaré comme service)
// =============================
$container->register('cache.menu', FilesystemTagAwareAdapter::class)
    ->setArguments([
        'menu',
        0,
        dirname(__DIR__) . '/var/cache/pools'
    ])
    ->setPublic(true);

// =============================
// 🔥 UserDataService
// =============================
$dataServiceDef = new Definition(UserDataService::class, [
    new Reference('doctrine.orm.default_entity_manager'),
    new Reference('cache.security'),
    new Reference('session')
]);
$dataServiceDef->setPublic(true);

$container->setDefinition('userData.service', $dataServiceDef);

// =============================
// 🔥 SecurityService
// =============================
$securityServiceDef = new Definition(SecurityService::class, [
    new Reference('userData.service')
]);
$securityServiceDef->setPublic(true);

$container->setDefinition('security.service', $securityServiceDef);

// =============================
// 🔥 MenuService
// =============================
$menuServiceDef = new Definition(MenuService::class, [
    new Reference('userData.service'),
    new Reference('cache.menu')
]);
$menuServiceDef->setPublic(true);

$container->setDefinition('menu.service', $menuServiceDef);

// =============================
// Charger YAML
// =============================
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.yaml');
$loader->load('parameters.yaml');

// =============================
// Pagination
// =============================
Paginator::useBootstrap();

// =============================
// Compiler + Dump
// =============================
$container->compile();

$dumper = new PhpDumper($container);
file_put_contents($cacheDir . '/Container.php', $dumper->dump(['class' => 'AppContainer']));
echo "✅ Conteneur compilé : {$cacheDir}/Container.php\n";

// ========================================
// ROUTES
// ✅ GAIN #1 : On génère 2 fichiers PHP natifs (matcher + generator)
//    Au lieu d'un serialize() lent, on obtient du PHP pur opcode-cacheable
// ========================================

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

// ✅ Dump du matcher compilé → PHP natif, require-able, absorbé par OPcache
$matcherDumper = new CompiledUrlMatcherDumper($collection);
file_put_contents(
    $cacheDir . '/url_matcher.php',
    "<?php\n\nreturn " . var_export($matcherDumper->getCompiledRoutes(), true) . ";\n"
);

// ✅ Dump du generator compilé → même bénéfice pour path() / url()
$generatorDumper = new CompiledUrlGeneratorDumper($collection);
file_put_contents(
    $cacheDir . '/url_generator.php',
    "<?php\n\nreturn " . var_export($generatorDumper->getCompiledRoutes(), true) . ";\n"
);

// ✅ On garde aussi le serialize() UNIQUEMENT pour le mode DEV (recompilation auto)
//    En PROD, ces fichiers ne sont jamais lus
file_put_contents($cacheDir . '/routes_dev.php', serialize($collection));

echo "✅ Routes compilées (matcher + generator) : {$cacheDir}/url_matcher.php\n";

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
