<?php

use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use Illuminate\Pagination\Paginator;
use App\Doctrine\EntityManagerFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require dirname(__DIR__) . '/vendor/autoload.php';

// --- 0. Cache directory ---
$cacheDir = dirname(__DIR__) . '/var/cache';
@mkdir($cacheDir, 0777, true);

// --- 1. Container ---
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

// --- 3. Compiler et dump PHP natif ---
$container->compile();
$dumper = new PhpDumper($container);
file_put_contents($cacheDir . '/Container.php', $dumper->dump([
    'class' => 'AppContainer'
]));

echo "✅ Conteneur compilé : {$cacheDir}/Container.php\n";
