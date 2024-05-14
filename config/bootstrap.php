<?php

use Twig\Environment;
use App\Service\TwigService;
use App\Controller\Controller;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;



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
$loader = new FilesystemLoader(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Views/templates');
       
        //$this->twig = new Environment($this->loader);
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());
        $twig->addExtension(new RoutingExtension($generator));
        // $this->strategy = new JsonManifestVersionStrategy('/path/to/manifest.json');
        // $this->package = new Package($this->strategy);
        // $this->twig->addExtension(new AssetExtension($this->package));

        Controller::setTwig($twig);
/**
 * transformation de langue
 */
$translator = new Translator('fr');
$translator->addLoader('array', new ArrayLoader());
$translator->addResource('array', [], 'fr');

/**
 * librairie pour la validation formulair
 */
$validator = Validation::createValidatorBuilder()
    ->enableAnnotationMapping(true)
    ->setDoctrineAnnotationReader(new AnnotationReader())
    ->getValidator();

$validatorExtension = new ValidatorExtension($validator);

Controller::setValidator($validatorExtension);


