<?php

use Twig\Environment;
use App\Twig\AppExtension;
use App\Twig\CarbonExtension;
use App\Controller\Controller;
use core\SimpleManagerRegistry;
use App\Twig\DeleteWordExtension;
use Symfony\Component\Form\Forms;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Illuminate\Pagination\Paginator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Validator\Validation;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension as CsrfCsrfExtension;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';


define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator');
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge');
define('VIEWS_DIR', realpath(__DIR__ . '/../views/templates'));



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
$generator = new UrlGenerator($collection, new RequestContext($_ENV['BASE_PATH_COURT']));

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
$twig = new Environment(new FilesystemLoader(array(
    VIEWS_DIR,
    VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
)), ['debug' => true]);


//configurer securite
$tokenStorage = new TokenStorage();
$accessDecisionManager = new AccessDecisionManager([new AffirmativeStrategy()]);
$authorizationChecker = new AuthorizationChecker($tokenStorage, $accessDecisionManager);

$session = new Session(new NativeSessionStorage());

$requestStack = new RequestStack();
$request = Request::createFromGlobals();
$requestStack->push($request);

$twig->addExtension(new TranslationExtension($translator));
$twig->addExtension(new DebugExtension());
$twig->addExtension(new RoutingExtension($generator));
$twig->addExtension(new FormExtension());
$twig->addExtension(new AppExtension($session, $requestStack, $tokenStorage, $authorizationChecker));
$twig->addExtension(new DeleteWordExtension());
$twig->addExtension(new CarbonExtension());

// Configurer le package pour le dossier 'public'
$publicPath = $_ENV['BASE_PATH_COURT'].'/public';
$packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));

// Ajouter l'extension Asset à Twig
$twig->addExtension(new AssetExtension($packages));

// Configure Form Renderer Engine and Runtime Loader
// $defaultFormTheme = 'form_div_layout.html.twig';
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));

$entitymanager = require_once dirname(__DIR__)."/doctrineBootstrap.php";

// Créer une instance de SimpleManagerRegistry
$managerRegistry = new SimpleManagerRegistry($entityManager);
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
