<?php

// bootstrap.php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
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




//Initialisation du conteneur de services
// $containerBuilder = new ContainerBuilder();
// $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
// $loader->load('services.yaml');

// On compile le container (résolution des dépendances)
$containerBuilder->compile();

return $containerBuilder;
