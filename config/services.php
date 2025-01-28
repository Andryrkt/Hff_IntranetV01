<?php
// config/services.php

use Twig\Environment;
use Doctrine\ORM\Tools\Setup;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use App\Factory\FrontController;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use App\Service\AccessControlService;
use App\Service\SessionManagerService;
use App\Factory\RouteCollectionFactory;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

// 1) On instancie le conteneur
$containerBuilder = new ContainerBuilder();

// 2) AnnotationReader
$containerBuilder
    ->register('annotation_reader', AnnotationReader::class)
;

// 3) CustomAnnotationClassLoader
$containerBuilder
    ->register('custom_annotation_class_loader', CustomAnnotationClassLoader::class)
    ->setArgument('$reader', $containerBuilder->get('annotation_reader'))
    // Si votre CustomAnnotationClassLoader attend d’autres arguments,
    // adaptez ici
;

// 4) FileLocator
$containerBuilder
    ->register('file_locator', FileLocator::class)
    // Le FileLocator peut prendre un tableau de chemins
    ->setArgument(0, [dirname(__DIR__) . '/src/Controller'])
;

// 5) AnnotationDirectoryLoader
$containerBuilder
    ->register('annotation_directory_loader', AnnotationDirectoryLoader::class)
    ->setArgument('$locator', $containerBuilder->get('file_locator'))
    ->setArgument('$loader', $containerBuilder->get('custom_annotation_class_loader'))
;

// 6) On définit un service "controller_routes" 
//    qui va être construit via la méthode "load(...)"
$definitionControllerRoutes = new Definition(RouteCollection::class);
$definitionControllerRoutes
    ->setFactory([$containerBuilder->getDefinition('annotation_directory_loader'), 'load'])
    ->setArguments([dirname(__DIR__).'/src/Controller'])
;
$containerBuilder->setDefinition('controller_routes', $definitionControllerRoutes);

// 7) Routes du dossier /src/Api/ (optionnel)
$definitionApiRoutes = new Definition(RouteCollection::class);
$definitionApiRoutes
    ->setFactory([$containerBuilder->getDefinition('annotation_directory_loader'), 'load'])
    ->setArguments([dirname(__DIR__).'/src/Api'])
;
$containerBuilder->setDefinition('api_routes', $definitionApiRoutes);

// 8) Fusionner les deux collections dans "route_collection"
$containerBuilder->register('route_collection_factory', RouteCollectionFactory::class);

$containerBuilder->register('route_collection', RouteCollection::class)
    ->setFactory([new Reference('route_collection_factory'), 'createRouteCollection'])
    ->setArguments([
        new Reference('controller_routes'),
        new Reference('api_routes'),
    ])
;

// 9) RequestContext
$containerBuilder
    ->register('request_context', RequestContext::class)
;

// 10) UrlMatcher
$containerBuilder
    ->register('url_matcher', UrlMatcher::class)
    ->setArguments([
        // NB: on utilise Reference pour laisser le container injecter
        new Reference('route_collection'),
        new Reference('request_context'),
    ])
    ->setPublic(false) // il peut rester privé si on l'injecte plus bas
;

// 11) ControllerResolver
$containerBuilder
    ->register('controller_resolver', ControllerResolver::class)
    ->setPublic(false)
;

// 12) ArgumentResolver
$containerBuilder
    ->register('argument_resolver', ArgumentResolver::class)
    ->setPublic(false)
;

// 13) FrontController (service public "app.front_controller")
$containerBuilder->register('app.front_controller', FrontController::class)
    ->setArguments([
        new Reference('url_matcher'),
        new Reference('controller_resolver'),
        new Reference('argument_resolver'),
    ])
    // On le rend public, car on va le récupérer en mode "entrypoint"
    ->setPublic(true)
;

/**
 * ENTITY MANAGER
 */
// 1) Définir un paramètre pour le chemin vers vos entités
$containerBuilder->setParameter('doctrine.entity_paths', [
    dirname(__DIR__).'/src/Entity'
]);

// 2) Définir un paramètre pour le mode dev (true/false)
$containerBuilder->setParameter('doctrine.is_dev_mode', true);

// 3) Définir les paramètres de connexion DB
$containerBuilder->setParameter('doctrine.db_params', [
    'driver'   => 'pdo_sqlsrv',
    'host'     => $_ENV["DB_HOST"], 
    'port'     => 1433,
    'user'     => $_ENV["DB_USERNAME"],
    'password' => $_ENV["DB_PASSWORD"],
    'dbname'   => $_ENV["DB_NAME"],
    'options'  => [],
]);

// 4) Service "annotation_reader"
$containerBuilder->register('doctrine.annotation_reader', AnnotationReader::class);

// 5) Service "annotation_driver"
$containerBuilder->register('doctrine.annotation_driver', AnnotationDriver::class)
    ->setArguments([
        new Reference('doctrine.annotation_reader'),
        '%doctrine.entity_paths%'
    ]);

// 6) Service "doctrine.config" retourné par Setup::createConfiguration(...)
$definitionConfig = new Definition(\Doctrine\ORM\Configuration::class);
$definitionConfig
    // On utilise ici la factory statique createConfiguration
    ->setFactory([\Doctrine\ORM\Tools\Setup::class, 'createConfiguration'])
    ->setArguments([
        '%doctrine.is_dev_mode%'  // isDevMode
        // vous pouvez aussi rajouter le chemin du cache ou proxy dir en 3e argument
    ])
    ->addMethodCall('setMetadataDriverImpl', [new Reference('doctrine.annotation_driver')])// On ajoute un appel de méthode pour insérer le driver d’annotations
;
$containerBuilder->setDefinition('doctrine.config', $definitionConfig);

// 7) Service "entity_manager"
$definitionEm = new Definition(EntityManager::class);
$definitionEm
    ->setFactory([EntityManager::class, 'create'])
    ->setArguments([
        '%doctrine.db_params%',
        new Reference('doctrine.config'),
    ])
    ->setPublic(true) // Rendez-le public si vous souhaitez l'obtenir via $container->get('entity_manager')
;
$containerBuilder->setDefinition('entity_manager', $definitionEm);

//8) Manager registrery pour manipuler le formulaire
$containerBuilder->register('manager_registry', SimpleManagerRegistry::class)
    ->setArguments([
        new Reference('entity_manager')
    ])
    ->setPublic(true);


/**
 * SSESSION SERVICE
 */
$containerBuilder->register('session_storage', NativeSessionStorage::class);

$containerBuilder->register('http_foundation.session', Session::class)
    ->setArguments([
        new Reference('session_storage')
    ]);

$containerBuilder->register('app.session_manager', SessionManagerService::class)
    ->setArguments([
        new Reference('http_foundation.session')
    ])
    ->setPublic(true);


/**
 * TWIG
 */
// 1) Loader
$loaderDefinition = new Definition(FilesystemLoader::class);
$loaderDefinition->setArguments([ 
    [realpath(__DIR__ . '/../views/templates'), realpath(__DIR__ . '/../vendor').'/symfony/twig-bridge'. '/Resources/views/Form'] 
]);
$containerBuilder->setDefinition('twig.loader', $loaderDefinition);

// 2) L'environnement Twig
$twigDefinition = new Definition(Environment::class);
$twigDefinition->setArguments([
    // le loader
    new Reference('twig.loader'),
    // options (ex. ['debug' => true])
    ['debug' => true],
]);

// 3.1) Ajouter des extensions (ex. DebugExtension)
$twigDefinition->addMethodCall('addExtension', [
    new Reference('twig.extension.debug')
]);

// 3.2) RoutingExtension
$twigDefinition->addMethodCall('addExtension', [
    new Reference('twig.extension.routing')
]);

// 3.3) FormExtension
$twigDefinition->addMethodCall('addExtension', [
    new Reference('twig.extension.form')
]);

// 3.4) FormExtension
$twigDefinition->addMethodCall('addExtension', [
    new Reference('twig.extension.form')
]);

// etc. vous pouvez ajouter d'autres extensions de la même façon
$containerBuilder->setDefinition('twig', $twigDefinition)->setPublic(true);

// 6) Déclarer les extensions elles-mêmes
$containerBuilder->register('twig.extension.debug', DebugExtension::class);
$containerBuilder->register('twig.extension.routing', RoutingExtension::class)
    ->setArguments([
        // le générateur d'URL, à supposer que vous l'ayez enregistré
        new Reference('routing.url_generator')
    ]);
$containerBuilder->register('twig.extension.form', FormExtension::class);

/**
 * TWIG FORM
 */
// 1) form_engine
$containerBuilder->register('twig.form_renderer_engine', \Symfony\Bridge\Twig\Form\TwigRendererEngine::class)
    ->setArguments([[$defaultFormTheme], new Reference('twig')]);

// 2) FormRenderer en “runtime”
$containerBuilder->register('twig.form_renderer', \Symfony\Component\Form\FormRenderer::class)
    ->setFactory(function() use ($containerBuilder) {
        // On récupère le service 'twig.form_renderer_engine'
        $formEngine = $containerBuilder->get('twig.form_renderer_engine');
        return new \Symfony\Component\Form\FormRenderer($formEngine);
    });

// 3) Ajout du RuntimeLoader
$twigDefinition->addMethodCall('addRuntimeLoader', [
    new \Twig\RuntimeLoader\FactoryRuntimeLoader([
        \Symfony\Component\Form\FormRenderer::class => new Reference('twig.form_renderer')
    ])
]);

/**
 * Access control service
 */
$containerBuilder->register('app.access_control_service', AccessControlService::class)
    ->setArguments([
        // 1) L’EntityManager
        new Reference('entity_manager'), 

        // 2) SessionManagerService
        new Reference('app.session_manager'),
    ])
    ->setPublic(true); 
    





    // On compile et on retourne le conteneur
$containerBuilder->compile();


return $containerBuilder;
