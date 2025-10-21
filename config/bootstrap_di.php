<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use App\Loader\CustomAnnotationClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\FormRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Illuminate\Pagination\Paginator;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// Charger les variables d'environnement
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// Charger les variables globales
require_once __DIR__ . '/listeConstructeur.php';

// Définir les variables d'environnement manquantes pour les tests CLI
if (!isset($_ENV['BASE_PATH_COURT'])) {
    $_ENV['BASE_PATH_COURT'] = '/Hffintranet';
}
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

// Configuration pour les tests CLI (session gérée dans test_di.php)

// Créer le conteneur de services
$container = new ContainerBuilder();

// Ajouter les paramètres de base manquants
$container->setParameter('kernel.project_dir', dirname(__DIR__));
$container->setParameter('kernel.cache_dir', dirname(__DIR__) . '/var/cache');
$container->setParameter('kernel.debug', true);

// Créer l'EntityManager manuellement AVANT de charger la configuration
$entityManager = require_once dirname(__DIR__) . "/doctrineBootstrap.php";

// Créer le ManagerRegistry pour Doctrine
$registry = new \core\SimpleManagerRegistry($entityManager);

// Enregistrer l'EntityManager comme service AVANT de charger la configuration
$container->set('doctrine.orm.default_entity_manager', $entityManager);

// Charger la configuration des services
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.yaml');

// Créer les services de base manuellement
$container->register('twig', 'Twig\Environment')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('form.factory', 'Symfony\Component\Form\FormFactory')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('router', 'Symfony\Component\Routing\Router')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('session', 'Symfony\Component\HttpFoundation\Session\Session')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('request_stack', 'Symfony\Component\HttpFoundation\RequestStack')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('security.token_storage', 'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('security.authorization_checker', 'Symfony\Component\Security\Core\Authorization\AuthorizationChecker')
    ->setSynthetic(true)
    ->setPublic(true);

// Enregistrer les services Twig AVANT de charger la configuration
$container->register('App\Twig\AppExtension', \App\Twig\AppExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('App\Service\navigation\MenuService', \App\Service\navigation\MenuService::class)
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('App\Service\navigation\BreadcrumbMenuService', \App\Service\navigation\BreadcrumbMenuService::class)
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('App\Twig\BreadcrumbExtension', \App\Twig\BreadcrumbExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('App\Twig\CarbonExtension', \App\Twig\CarbonExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);

$container->register('App\Twig\DeleteWordExtension', \App\Twig\DeleteWordExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);

// Charger les paramètres
$loader->load('parameters.yaml');

// L'EntityManager est déjà assigné plus haut

// Compiler le conteneur
$container->compile();

// Créer les services manuellement (comme dans l'ancien bootstrap)
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([
    dirname(__DIR__) . '/Views/templates',
    dirname(__DIR__) . '/vendor/symfony/twig-bridge/Resources/views/Form',
]), ['debug' => true]);

$container->set('twig', $twig);



// Create the form factory with container integration
$formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
    ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
    ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(\Symfony\Component\Validator\Validation::createValidator()))
    ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
    ->addExtension(new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($container->get('doctrine')))
    ->addExtension(new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension($container, [], []))
    ->getFormFactory();

$container->set('form.factory', $formFactory);


// Créer et assigner les autres services
$session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage());
$container->set('session', $session);

$requestStack = new \Symfony\Component\HttpFoundation\RequestStack();
$container->set('request_stack', $requestStack);

$tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
$container->set('security.token_storage', $tokenStorage);

$accessDecisionManager = new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager([
    new \Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy()
]);
$authorizationChecker = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker($tokenStorage, $accessDecisionManager);
$container->set('security.authorization_checker', $authorizationChecker);

// Créer et assigner le translator
$translator = new \Symfony\Component\Translation\Translator('fr_FR');
$translator->addLoader('xlf', new \Symfony\Component\Translation\Loader\XliffFileLoader());
$container->set('translator', $translator);

// Récupérer les services du conteneur
$session = $container->get('session');
$requestStack = $container->get('request_stack');
$tokenStorage = $container->get('security.token_storage');
$authorizationChecker = $container->get('security.authorization_checker');



// Créer et assigner les services Twig APRÈS la compilation
$appExtension = new \App\Twig\AppExtension($session, $requestStack, $tokenStorage, $authorizationChecker, $entityManager);
$container->set('App\Twig\AppExtension', $appExtension);

$menuService = new \App\Service\navigation\MenuService($entityManager);
$container->set('App\Service\navigation\MenuService', $menuService);

$breadcrumbMenuService = new \App\Service\navigation\BreadcrumbMenuService($menuService);
$container->set('App\Service\navigation\BreadcrumbMenuService', $breadcrumbMenuService);

$breadcrumbExtension = new \App\Twig\BreadcrumbExtension($breadcrumbMenuService);
$container->set('App\Twig\BreadcrumbExtension', $breadcrumbExtension);

$carbonExtension = new \App\Twig\CarbonExtension();
$container->set('App\Twig\CarbonExtension', $carbonExtension);

$deleteWordExtension = new \App\Twig\DeleteWordExtension();
$container->set('App\Twig\DeleteWordExtension', $deleteWordExtension);

// Créer la requête et la réponse
$request = Request::createFromGlobals();
$response = new Response();

// Vérifier la casse du préfixe /Hffintranet/
$pathInfo = $request->getPathInfo();
if (stripos($pathInfo, '/hffintranet') === 0 && strpos($pathInfo, '/Hffintranet') !== 0) {
    $correctUrl = preg_replace('#^/hffintranet#i', '/Hffintranet', $pathInfo);
    $redirectResponse = new \Symfony\Component\HttpFoundation\RedirectResponse($correctUrl, 301);
    $redirectResponse->send();
    exit; // on arrête le script après la redirection
}

// Charger les routes
$routeLoader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Controller/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);
$controllerCollection = $routeLoader->load(dirname(__DIR__) . '/src/Controller/');

// Charger les routes API
$apiLoader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Api/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);
$apiCollection = $apiLoader->load(dirname(__DIR__) . '/src/Api/');

// Configurer le contexte de requête
$context = new RequestContext();
$context->fromRequest($request);

// Fusionner les collections de routes
$collection = new RouteCollection();
$collection->addCollection($controllerCollection);
$collection->addCollection($apiCollection);

// ➡️ Ajoute ce bloc juste ici
foreach ($collection as $route) {
    $route->setOption('case_sensitive', false);
}

// Créer le UrlGenerator avec la vraie collection de routes
$urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);
$container->set('router', $urlGenerator);

// Configurer le matcher d'URL
$matcher = new UrlMatcher($collection, $context);

// Configurer les resolvers
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Configurer Twig avec les extensions
$twig->addExtension(new DebugExtension());
$twig->addExtension(new TranslationExtension($container->get('translator')));
$twig->addExtension(new RoutingExtension($urlGenerator));
$twig->addExtension(new FormExtension());
$twig->addExtension($container->get('App\Twig\AppExtension'));
$twig->addExtension($container->get('App\Twig\BreadcrumbExtension'));
$twig->addExtension($container->get('App\Twig\CarbonExtension'));
$twig->addExtension($container->get('App\Twig\DeleteWordExtension'));

// Configurer l'extension Asset
$publicPath = $_ENV['BASE_PATH_COURT'] . '/public';
$packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));
$twig->addExtension(new AssetExtension($packages));

// Configurer le moteur de rendu des formulaires
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));

// Configurer la pagination
Paginator::useBootstrap();

// Le contrôleur principal utilise maintenant l'injection de dépendances
// Plus besoin de configuration statique

// Stocker le conteneur dans une variable globale pour y accéder
global $container;

// Retourner les services principaux
return [
    'container' => $container,
    'entityManager' => $entityManager,
    'twig' => $twig,
    'formFactory' => $formFactory,
    'urlGenerator' => $urlGenerator,
    'session' => $session,
    'requestStack' => $requestStack,
    'tokenStorage' => $tokenStorage,
    'authorizationChecker' => $authorizationChecker,
    'matcher' => $matcher,
    'controllerResolver' => $controllerResolver,
    'argumentResolver' => $argumentResolver,
    'routeCollection' => $collection,
];
