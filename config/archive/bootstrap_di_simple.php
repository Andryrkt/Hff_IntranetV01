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

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// Charger les variables d'environnement
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// Définir les variables d'environnement manquantes
if (!isset($_ENV['BASE_PATH_COURT'])) {
    $_ENV['BASE_PATH_COURT'] = '/Hffintranet';
}

// Créer le conteneur de services
$container = new ContainerBuilder();

// Créer l'EntityManager manuellement
$entityManager = require_once dirname(__DIR__) . "/doctrineBootstrap.php";

// Créer le service EntityManager dans le conteneur
$container->register('doctrine.orm.entity_manager', get_class($entityManager))
    ->setSynthetic(true)
    ->setPublic(true);

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

// Créer et assigner les services manuellement
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([
    dirname(__DIR__) . '/Views/templates',
]), ['debug' => true]);

$formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
    ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
    ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(\Symfony\Component\Validator\Validation::createValidator()))
    ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
    ->getFormFactory();

$session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage());
$requestStack = new \Symfony\Component\HttpFoundation\RequestStack();
$tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
$accessDecisionManager = new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager([
    new \Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy()
]);
$authorizationChecker = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker($tokenStorage, $accessDecisionManager);

// Assigner les services au conteneur
$container->set('doctrine.orm.entity_manager', $entityManager);
$container->set('twig', $twig);
$container->set('form.factory', $formFactory);
$container->set('session', $session);
$container->set('request_stack', $requestStack);
$container->set('security.token_storage', $tokenStorage);
$container->set('security.authorization_checker', $authorizationChecker);

// Créer la requête et la réponse
$request = Request::createFromGlobals();
$response = new Response();

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

// Créer le UrlGenerator avec la vraie collection de routes
$urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);
$container->set('router', $urlGenerator);

// Configurer le matcher d'URL
$matcher = new UrlMatcher($collection, $context);

// Configurer les resolvers
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Stocker le conteneur dans une variable globale
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
