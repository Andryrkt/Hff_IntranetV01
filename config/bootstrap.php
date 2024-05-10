<?php

use App\Service\TwigService;
use App\Controller\Controller;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;



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
$generator = new UrlGenerator($collection, new RequestContext('/Hffintranet'));

//$pathInfo = $_SERVER['PATH_INFO'] ?? '/';

//$pathInfo = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';

Controller::setTwig($generator);
