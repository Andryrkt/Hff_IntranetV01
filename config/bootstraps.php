<?php

use App\Controller\Controller;
use App\Loader\CustomAnnotationClassLoader;
use App\Twig\AppExtension;
use App\Twig\CarbonExtension;
use App\Twig\DeleteWordExtension;
use core\SimpleManagerRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Illuminate\Pagination\Paginator;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension as CsrfCsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';


define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator');
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge');
define('VIEWS_DIR', realpath(__DIR__ . '/../views/templates'));

define('CHEMIN_DE_BASE', 'C:/wamp64/www/Hffintranet');



$request = Request::createFromGlobals();
$response = new Response();

/** ROUTE */
// Charger les routes du dossier 'Controller'
$loader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Controller/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);
$controllerCollection = $loader->load(dirname(__DIR__) . '/src/Controller/');

// Charger les routes du dossier 'Api'
$apiLoader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Api/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);
$apiCollection = $apiLoader->load(dirname(__DIR__) . '/src/Api/');

// Fusionner les deux collections
$collection = new RouteCollection();
$collection->addCollection($controllerCollection);
$collection->addCollection($apiCollection);

// Configurer le UrlMatcher
$matcher = new UrlMatcher($collection, new RequestContext(''));

// Resolver and argument resolver
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

/** TWIG */
// URL Generator for use in Twig
$generator = new UrlGenerator($collection, new RequestContext('/Hffintranet'));

//secuiter csrf
$csrfTokenManager = new CsrfTokenManager();

// Form Validator
$validator = Validation::createValidator();


// Translator
$translator = new Translator('fr_Fr');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
$translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

// Form Factory
$formFactoryBuilder = new FormFactoryBuilder();
$formFactoryBuilder->addExtension(new CoreExtension());
$formFactoryBuilder->addExtension(new ValidatorExtension($validator));
$formFactoryBuilder->addExtension(new HttpFoundationExtension());

$formFactory = $formFactoryBuilder->getFormFactory();

// Twig Environment
$twig = new Environment(new FilesystemLoader([
    VIEWS_DIR,
    VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
]), ['debug' => true]);


//configurer securite
$tokenStorage = new TokenStorage();
$accessDecisionManager = new AccessDecisionManager([new AffirmativeStrategy()]);
$authorizationChecker = new AuthorizationChecker($tokenStorage, $accessDecisionManager);

$session = new Session(new NativeSessionStorage());

$requestStack = new RequestStack();
$request = Request::createFromGlobals();
$requestStack->push($request);

$twig->addExtension(new TranslationExtension($translator));
//$loader = new FilesystemLoader('C:\wamp64\www\Hffintranet\Views\templates');
//$twig = new Environment($loader, ['debug' => true]);
$twig->addExtension(new DebugExtension());
$twig->addExtension(new RoutingExtension($generator));
$twig->addExtension(new FormExtension());
$twig->addExtension(new AppExtension($session, $requestStack, $tokenStorage, $authorizationChecker));
$twig->addExtension(new DeleteWordExtension());
$twig->addExtension(new CarbonExtension());

// Configurer le package pour le dossier 'public'
$publicPath = '/Hffintranet/public';
$packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));
$twig->addExtension(new AssetExtension($packages));// Ajouter l'extension Asset à Twig


$entitymanager = require_once dirname(__DIR__)."/doctrineBootstrap.php";

// Créer une instance de SimpleManagerRegistry
$managerRegistry = new SimpleManagerRegistry($entityManager);

// Configure Form Renderer Engine and Runtime Loader
// $defaultFormTheme = 'form_div_layout.html.twig';
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));


// Set up the Form component
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfCsrfExtension($csrfTokenManager))
    ->addExtension(new ValidatorExtension($validator))
    ->addExtension(new CoreExtension())
    ->addExtension(new HttpFoundationExtension())
    ->addExtension(new DoctrineOrmExtension($managerRegistry))
    ->getFormFactory();

Paginator::useBootstrap();

//envoyer twig au controller
Controller::setTwig($twig);

Controller::setValidator($formFactory);

Controller::setGenerator($generator);

Controller::setEntity($entityManager);

//Controller::setPaginator($paginator);













/////////////////////////////////////////////////////////////////////////////////////
