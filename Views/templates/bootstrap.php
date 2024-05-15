<?php

use Twig\Environment;
use Pimple\Psr11\Container;
use App\Service\TwigService;
use App\Controller\Controller;
use Symfony\Component\Form\Forms;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Config\FileLocator;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Validator\Validation;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;



require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$request = Request::createFromGlobals();

$response = new Response();

$loader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Controller/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);

$collection = $loader->load(dirname(__DIR__) . '/src/Controller/');

$matcher = new UrlMatcher($collection, new RequestContext('', $_SERVER['REQUEST_METHOD']));

$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();
/**
 * pour l'utilisation du fonction path dans twig
 */
$generator = new UrlGenerator($collection, new RequestContext('/Hffintranet'));

//$pathInfo = $_SERVER['PATH_INFO'] ?? '/';

//$pathInfo = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';


//Controller::setTwig($generator);



/**
 * config twig
 */
$loader = new FilesystemLoader('C:\wamp64\www\Hffintranet\Views\templates');
$loader->addPath('C:/wamp64/www/Hffintranet/vendor/symfony/twig-bridge/Resources/views/Form/', 'Form');
       
        //$this->twig = new Environment($this->loader);
        $twig = new Environment($loader, ['debug' => true]);


        // Créer l'usine de formulaires
$formFactory = Forms::createFormFactoryBuilder()
->addExtension(new HttpFoundationExtension())
    ->getFormFactory();

// Définir les chemins vers les templates de formulaire par défaut
$defaultFormTheme = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_horizontal_layout.html.twig';
$twigRendererEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$formRenderer = new FormRenderer($twigRendererEngine);
/**
 * configuration de la langue
 */
$translator = new Translator('fr_FR');
$translator->addLoader('array', new ArrayLoader());
$translator->addResource('array', [], 'fr_FR');

        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new RoutingExtension($generator));
        $twig->addExtension(new FormExtension());
        $twig->addExtension(new TranslationExtension($translator));
        // $this->strategy = new JsonManifestVersionStrategy('/path/to/manifest.json');
        // $this->package = new Package($this->strategy);
        // $this->twig->addExtension(new AssetExtension($this->package));


// Ajouter le FormRenderer à Twig en tant que runtime
$twig->addRuntimeLoader(new \Twig\RuntimeLoader\FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formRenderer) {
        return $formRenderer;
    },
]));

        //envoyer twig au controller
        Controller::setTwig($twig);






/**
 * configuration validation formulaire
 */
$validator = Validation::createValidatorBuilder()
    ->enableAnnotationMapping(true)
    ->setDoctrineAnnotationReader(new AnnotationReader())
    ->getValidator();

$validatorExtension = new ValidatorExtension($validator);

Controller::setValidator($validatorExtension);

// require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// use Twig\Environment;
// use App\Controller\Controller;
// use Symfony\Component\Form\Forms;
// use Twig\Loader\FilesystemLoader;
// use Twig\Extension\DebugExtension;
// use Symfony\Component\Form\FormRenderer;
// use Symfony\Component\Config\FileLocator;
// use App\Loader\CustomAnnotationClassLoader;
// use Symfony\Component\Validator\Validation;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Routing\RequestContext;
// use Symfony\Component\Translation\Translator;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\RouteCollection;
// use Twig\RuntimeLoader\ContainerRuntimeLoader;
// use Symfony\Bridge\Twig\Extension\FormExtension;
// use Symfony\Bridge\Twig\Form\TwigRendererEngine;
// use Doctrine\Common\Annotations\AnnotationReader;
// use Symfony\Component\Routing\Matcher\UrlMatcher;
// use Symfony\Bridge\Twig\Extension\RoutingExtension;
// use Symfony\Component\DependencyInjection\Reference;
// use Symfony\Component\Routing\Generator\UrlGenerator;
// use Symfony\Component\Translation\Loader\ArrayLoader;
// use Symfony\Component\DependencyInjection\ContainerBuilder;
// use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
// use Symfony\Component\HttpKernel\Controller\ControllerResolver;
// use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
// use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

// $request = Request::createFromGlobals();
// $response = new Response();

// // Load and configure routes
// $loader = new AnnotationDirectoryLoader(
//     new FileLocator(dirname(__DIR__) . '/src/Controller/'),
//     new CustomAnnotationClassLoader(new AnnotationReader())
// );
// $collection = $loader->load(dirname(__DIR__) . '/src/Controller/');
// $matcher = new UrlMatcher($collection, new RequestContext('', $request->getMethod()));

// $controllerResolver = new ControllerResolver();
// $argumentResolver = new ArgumentResolver();

// // UrlGenerator for routing in Twig
// $generator = new UrlGenerator($collection, new RequestContext('/Hffintranet'));

// // Dependency Injection Container setup
// $container = new ContainerBuilder();

// // Twig and Form setup
// $container->register('twig.loader', FilesystemLoader::class)
//     ->setArgument(0, dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views/templates');
// $container->register('twig.environment', Environment::class)
//     ->setArgument(0, new Reference('twig.loader'))
//     ->addArgument(['debug' => true])
//     ->addMethodCall('addExtension', [new DebugExtension()])
//     ->addMethodCall('addExtension', [new FormExtension()])
//     ->addMethodCall('addExtension', [new RoutingExtension($generator)]);

// // Form Renderer setup
// $formFactory = Forms::createFormFactory();
// $twigRendererEngine = new TwigRendererEngine(['form_div_layout.html.twig'], $container->get('twig.environment'));
// $container->register('form.renderer', FormRenderer::class)
//     ->setArguments([$twigRendererEngine, null])
//     ->setPublic(true);
// $container->setAlias(FormRenderer::class, 'form.renderer');

// // Runtime Loader for Twig
// $container->register('twig.runtime_loader', ContainerRuntimeLoader::class)
//     ->setArgument(0, $container);
// $container->getDefinition('twig.environment')
//     ->addMethodCall('addRuntimeLoader', [new Reference('twig.runtime_loader')]);

// // Translator setup
// $translator = new Translator('fr');
// $translator->addLoader('array', new ArrayLoader());
// $translator->addResource('array', [], 'fr');

// // Validator setup
// $validator = Validation::createValidatorBuilder()
//     ->enableAnnotationMapping(true)
//     ->setDoctrineAnnotationReader(new AnnotationReader())
//     ->getValidator();
// $validatorExtension = new ValidatorExtension($validator);

// // Finalize configuration and make services available
// $container->compile();
// $twig = $container->get('twig.environment');
// $formRenderer = $container->get('form.renderer');

// Controller::setTwig($twig);
// Controller::setValidator($validatorExtension);

