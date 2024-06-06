<?php

use Twig\Environment;

use App\Model\ProfilModel;

use Doctrine\ORM\Tools\Setup;
use App\Controller\Controller;
use core\SimpleManagerRegistry;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\Forms;
use Twig\Loader\FilesystemLoader;
use Knp\Component\Pager\Paginator;
use Twig\Extension\DebugExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Config\FileLocator;
use Doctrine\Migrations\DependencyFactory;
use App\Loader\CustomAnnotationClassLoader;

use Symfony\Component\Validator\Validation;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Translator;

use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bridge\Twig\Extension\CsrfExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension as CsrfCsrfExtension;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator');
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge');
define('VIEWS_DIR', realpath(__DIR__ . '/../views/templates'));

$request = Request::createFromGlobals();
$response = new Response();

// Configure the URL matcher
$loader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Controller/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);
$collection = $loader->load(dirname(__DIR__) . '/src/Controller/');
$matcher = new UrlMatcher($collection, new RequestContext(''));

// Resolver and argument resolver
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

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
$twig = new Environment(new FilesystemLoader(array(
    VIEWS_DIR,
    VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
)), ['debug' => true]);


$twig->addExtension(new TranslationExtension($translator));
//$loader = new FilesystemLoader('C:\wamp64\www\Hffintranet\Views\templates');
//$twig = new Environment($loader, ['debug' => true]);
$twig->addExtension(new DebugExtension());
$twig->addExtension(new RoutingExtension($generator));
$twig->addExtension(new FormExtension());


// Configure Form Renderer Engine and Runtime Loader
// $defaultFormTheme = 'form_div_layout.html.twig';
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));

$session = new Session(new NativeSessionStorage());

$requestStack = new RequestStack();
$request = Request::createFromGlobals();
$requestStack->push($request);

// Initialisation du conteneur de services
$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('services.yaml');

// Initialisation de la session
//$session = new Session();


// Initialisation des services nécessaires
// $containerBuilder->set('session', $session);
// $containerBuilder->set('app.profil_model', new ProfilModel()); // Assurez-vous que ProfilModel est correctement défini
$containerBuilder->compile();



require_once dirname(__DIR__)."/doctrineBootstrap.php";

// Set up the Form component
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfCsrfExtension($csrfTokenManager))
    ->addExtension(new ValidatorExtension($validator))
    ->addExtension(new CoreExtension())
    ->addExtension(new HttpFoundationExtension())
    ->addExtension(new DoctrineOrmExtension($managerRegistry))
    ->getFormFactory();

// Configurer KnpPaginator
// $eventDispatcher = new EventDispatcher();
// $paginator = new Paginator($eventDispatcher, $requestStack);

//envoyer twig au controller
Controller::setTwig($twig);

Controller::setValidator($formFactory);

Controller::setGenerator($generator);

Controller::setEntity($entityManager);

//Controller::setPaginator($paginator);













/////////////////////////////////////////////////////////////////////////////////////
