<?php

use Twig\Environment;

use App\Controller\Controller;

use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
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

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Translation\Loader\ArrayLoader;

use Symfony\Component\Form\Extension\Core\CoreExtension;

use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

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

// Form Validator
$validator = Validation::createValidatorBuilder()
    ->enableAnnotationMapping(true)
    ->setDoctrineAnnotationReader(new AnnotationReader())
    ->getValidator();

// Translator
$translator = new Translator('fr_FR');
$translator->addLoader('array', new ArrayLoader());

// Form Factory
$formFactoryBuilder = new FormFactoryBuilder();
$formFactoryBuilder->addExtension(new CoreExtension());
$formFactoryBuilder->addExtension(new ValidatorExtension($validator));
$formFactoryBuilder->addExtension(new HttpFoundationExtension());

$formFactory = $formFactoryBuilder->getFormFactory();

// Twig Environment
$loader = new FilesystemLoader('C:\wamp64\www\Hffintranet\Views\templates');
$twig = new Environment($loader, ['debug' => true]);
$twig->addExtension(new DebugExtension());
$twig->addExtension(new RoutingExtension($generator));
$twig->addExtension(new FormExtension());
$twig->addGlobal('translator', $translator);

// Configure Form Renderer Engine and Runtime Loader
$defaultFormTheme = 'form_div_layout.html.twig';
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));



        //envoyer twig au controller
        Controller::setTwig($twig);













/////////////////////////////////////////////////////////////////////////////////////
