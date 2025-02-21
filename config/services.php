<?php
// config/services.php

use Monolog\Logger;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Tools\Setup;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;
use App\Factory\FrontController;
use Symfony\Component\Ldap\Ldap;
use App\Twig\DeleteWordExtension;
use Symfony\Component\Form\Forms;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Monolog\Handler\StreamHandler; 
use Symfony\Component\Finder\Finder;
use App\Service\AccessControlService;
use Symfony\Component\Asset\Packages;
use App\Controller\AbstractController;
use App\Service\SessionManagerService;
use App\Factory\RouteCollectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension as CsrfCsrfExtension;

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
    ->register('controller_resolver', ContainerControllerResolver::class)
    ->setArguments([new Reference('service_container')])
    ->setPublic(true)
;

// 12) ArgumentResolver
$containerBuilder
    ->register('argument_resolver', ArgumentResolver::class)
    ->setPublic(false)
;

// 13) Enregistrement du logger (Monolog)
$containerBuilder->register('logger', Logger::class)
    ->setArguments(['app'])
    ->addMethodCall('pushHandler', [new StreamHandler(__DIR__ . '/../var/logs/app.log', Logger::DEBUG)])
    ->setPublic(true);
// Alias pour que LoggerInterface pointe sur 'logger'
$containerBuilder->setAlias(LoggerInterface::class, 'logger')
    ->setPublic(true);

// 14) FrontController (service public "app.front_controller")
$containerBuilder->register('app.front_controller', FrontController::class)
    ->setArguments([
        new Reference('url_matcher'),
        new Reference('controller_resolver'),
        new Reference('argument_resolver'),
        new Reference('logger'),
    ])
    // On le rend public, car on va le récupérer en mode "entrypoint"
    ->setPublic(true)
;

// 15) UrlGenerator
$containerBuilder->register('request_context', RequestContext::class)
    ->addMethodCall('setBaseUrl', ['/Hffintranet']);
$containerBuilder->register('routing.url_generator', UrlGenerator::class)
    ->setArguments([
        new Reference('route_collection'),
        new Reference('request_context')
    ])
    ->setPublic(true);
$containerBuilder->setAlias(UrlGeneratorInterface::class, 'routing.url_generator')
    ->setPublic(true);

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

    //9) alias

$containerBuilder->setAlias(EntityManagerInterface::class, 'entity_manager')
    ->setPublic(true);


/**
 * REQUEST
 */
// 1) Définition du service "request"
$requestDefinition = new Definition(Request::class);
// On utilise la factory statique createFromGlobals()
$requestDefinition->setFactory([Request::class, 'createFromGlobals']);
// (facultatif) Rendez-le public si vous voulez le récupérer directement
$requestDefinition->setPublic(true);

$containerBuilder->setDefinition('request', $requestDefinition);

// 2) Définition du service "request_stack"
$requestStackDefinition = new Definition(RequestStack::class);
// On empile la requête via un appel de méthode
$requestStackDefinition->addMethodCall('push', [
    // Référence au service "request"
    new Reference('request')
]);
$requestStackDefinition->setPublic(true);

$containerBuilder->setDefinition('request_stack', $requestStackDefinition);


/**
 * SSESSION SERVICE
 */
$containerBuilder->register('session_storage', NativeSessionStorage::class);

$containerBuilder->register('http_foundation.session', Session::class)
    ->setArguments([
        new Reference('session_storage')
    ])
    ->setPublic(true);
$containerBuilder->setAlias(SessionInterface::class, 'http_foundation.session')
->setPublic(true);

$containerBuilder->register('app.session_manager', SessionManagerService::class)
    ->setArguments([
        new Reference('http_foundation.session')
    ])
    ->setPublic(true);

/**
 * INITIALISATION EXTENSION TWIG
 */
//1) AppExtension
$containerBuilder->register('app.app_extension', \App\Twig\AppExtension::class)
    ->setArguments([
        new Reference('http_foundation.session'), 
        new Reference('request_stack'),
        new Reference('entity_manager'),
    ]);

//2) DeletewordExtension
$containerBuilder->register('app.delete_word_extension', DeleteWordExtension::class);

//3) AssetExtension
// 3.1) On déclare un paramètre pour le chemin public
$containerBuilder->setParameter('asset.public_path', '/Hffintranet/public');

// 3.2) Enregistrer la EmptyVersionStrategy
$containerBuilder->register('asset.version_strategy.empty', EmptyVersionStrategy::class)
    ->setPublic(false);

// 3.3) Enregistrer le PathPackage
//    PathPackage::__construct(string $basePath, VersionStrategyInterface $versionStrategy, ContextInterface $context = null)
$containerBuilder->register('asset.path_package', PathPackage::class)
    ->setArguments([
        '%asset.public_path%',                                // le chemin
        new Reference('asset.version_strategy.empty'),        // la stratégie
        // Optionnel : un troisième argument pour le contexte (si nécessaire)
    ])
    ->setPublic(false);

// 3.4) Enregistrer "Packages" 
//    Packages::__construct(PackageInterface $defaultPackage, array $packages = [])
//    On utilise le PathPackage comme "package" principal ou par défaut
$containerBuilder->register('asset.packages', Packages::class)
    ->setArguments([
        new Reference('asset.path_package'), // defaultPackage
        []                                   // ou un tableau de packages nommés
    ])
    ->setPublic(true);
//3.5)
    $containerBuilder->register('twig.extension.asset', \Symfony\Bridge\Twig\Extension\AssetExtension::class)
    ->setArguments([
        new Reference('asset.packages')
    ])
    ->setPublic(false);
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
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.debug')]);

// 3.2) RoutingExtension
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.routing')]);

// 3.3) FormExtension
$twigDefinition->addMethodCall('addExtension', [new Reference('twig.extension.form')]);

//3.4)AppExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('app.app_extension') ]);

//3.5)DeleteWordExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('app.delete_word_extension') ]);

//3.6) AssetExtension
$twigDefinition->addMethodCall('addExtension', [ new Reference('twig.extension.asset')]);

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

// Alias pour autowiring de Twig
$containerBuilder->setAlias(Environment::class, 'twig')
    ->setPublic(true);
/**
 * TWIG FORM
 */
// 1.1) Service “twig.form_renderer_engine”
//      qui reçoit un tableau de thèmes + le service `twig`
$containerBuilder->register('twig.form_renderer_engine', TwigRendererEngine::class)
    ->setArguments([
        [ 'bootstrap_5_layout.html.twig' ],  // ou votre autre thème
        new Reference('twig'),               // le service twig existant
    ])
    ->setPublic(false);

// 1.2) Service “twig.form_renderer”
//      qui construit le FormRenderer basé sur l’engine ci-dessus
$containerBuilder->register('twig.form_renderer', FormRenderer::class)
    ->setArguments([
        new Reference('twig.form_renderer_engine'),
        // Si vous utilisez un CsrfTokenManager pour les formulaires, 
        // vous pouvez l'ajouter ici en second argument.
    ])
    ->setPublic(false);

// 1.3) Service “twig.form_runtime_loader”
//      un FactoryRuntimeLoader qui associe la classe FormRenderer::class
//      à l’instance twig.form_renderer
$containerBuilder->register('twig.form_runtime_loader', FactoryRuntimeLoader::class)
    ->setArguments([[
        FormRenderer::class => new Reference('twig.form_renderer')
    ]])
    ->setPublic(false);

// 1.4) Ajouter ce runtime loader à Twig via un appel de méthode
$containerBuilder->getDefinition('twig') // supposez que "twig" soit déjà défini
    ->addMethodCall('addRuntimeLoader', [
        new Reference('twig.form_runtime_loader')
    ]);

/**
 * TWIG FORM FACTORY
 */
// 1) Enregistrement
// 1-2) Enregistrement du service "csrf_token_manager"
$containerBuilder->register('csrf_token_manager', CsrfTokenManager::class)
->setPublic(true);
//1-3) Enregistrement du service "validator"
$containerBuilder->register('validator', \Symfony\Component\Validator\Validator\ValidatorInterface::class)
->setPublic(true);

// a) Extension CSRF
$containerBuilder->register('form.extension.csrf', CsrfCsrfExtension::class)
    ->setArguments([
        new Reference('csrf_token_manager')  // Supposez que vous ayez un service `csrf_token_manager`
    ])
    ->setPublic(false);

// b) Extension Validator
$containerBuilder->register('form.extension.validator', ValidatorExtension::class)
    ->setArguments([
        new Reference('validator') // Supposez que vous ayez un service `validator`
    ])
    ->setPublic(false);

// c) Extension Core
$containerBuilder->register('form.extension.core', CoreExtension::class)
    ->setPublic(false);

// d) Extension HttpFoundation
$containerBuilder->register('form.extension.http_foundation', HttpFoundationExtension::class)
    ->setPublic(false);

// e) Extension Doctrine
$containerBuilder->register('form.extension.doctrine_orm', DoctrineOrmExtension::class)
    ->setArguments([
        new Reference('manager_registry') // Supposez que vous ayez `manager_registry`
    ])
    ->setPublic(false);


    // Service "form.factory_builder"
$containerBuilder->register('form.factory_builder', FormFactoryBuilderInterface::class)
// On utilise la factory statique "Forms::createFormFactoryBuilder()"
->setFactory([Forms::class, 'createFormFactoryBuilder'])
// On ajoute les extensions
->addMethodCall('addExtension', [new Reference('form.extension.csrf')])
->addMethodCall('addExtension', [new Reference('form.extension.validator')])
->addMethodCall('addExtension', [new Reference('form.extension.core')])
->addMethodCall('addExtension', [new Reference('form.extension.http_foundation')])
->addMethodCall('addExtension', [new Reference('form.extension.doctrine_orm')])
->setPublic(false);

// Service "form.factory"
$containerBuilder->register('form.factory', FormFactoryInterface::class)
    ->setFactory([new Reference('form.factory_builder'), 'getFormFactory'])
    ->setPublic(true);
    
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
    

    /** 
     * Enregistrer AbstractController dans le conteneur
     */ 
// 1️⃣ Enregistrer `AbstractController`
// $containerBuilder->register('abstract_controller', AbstractController::class)
// ->setArguments([new Reference('service_container')])
// ->setPublic(true);

// 2️⃣ Automatiser l'enregistrement de tous les contrôleurs
$finder = new Finder();
$finder->files()->in(dirname(__DIR__) . '/src/Controller')->name('*.php')->depth('>= 0');

foreach ($finder as $file) {
    $relativePath = substr($file->getRealPath(), strlen(dirname(__DIR__)) + 5, -4); // On enlève "src/"
$className = 'App\\' . str_replace(['/', '\\'], '\\', $relativePath);

    // dd($className);

    if (!class_exists($className)) {
        continue;
    }

    $containerBuilder->register($className, $className)
        ->setAutowired(true) // Permet l'injection automatique des dépendances
        ->setAutoconfigured(true) // Active l'injection des dépendances
        ->setPublic(true);
}





/**
 * LDAP
 */
// Enregistrer le service LDAP
$containerBuilder->register('ldap', Ldap::class)
    ->setFactory([Ldap::class, 'create'])
    ->setArguments([
        'ext_ldap',
        [
            'host'       => '192.168.0.1',   // Votre adresse LDAP
            'port'       => 389,             // Le port LDAP
            'encryption' => 'none',            // 'tls' si vous utilisez TLS, sinon null
            'options'    => [
                'protocol_version' => 3,     // Version du protocole LDAP
                'referrals'        => false, // Désactiver les referrals
            ],
        ]
    ])
    ->setPublic(true);
// Alias pour autowiring
$containerBuilder->setAlias(Ldap::class, 'ldap')->setPublic(true);

    $containerBuilder->register(\App\Service\ldap\MyLdapService::class, \App\Service\ldap\MyLdapService::class)
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true);

    // On compile et on retourne le conteneur
$containerBuilder->compile();

date_default_timezone_set('Indian/Antananarivo');

return $containerBuilder;
