<?php

use App\Service\TwigService;
use App\Controller\Controller;
use Symfony\Component\Config\FileLocator;
use App\Loader\CustomAnnotationClassLoader;
use Symfony\Component\Routing\RequestContext;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;



require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loader = new AnnotationDirectoryLoader(
    new FileLocator(dirname(__DIR__) . '/src/Controller/'),
    new CustomAnnotationClassLoader(new AnnotationReader())
);

$collection = $loader->load(dirname(__DIR__) . '/src/Controller/');

$matcher = new UrlMatcher($collection, new RequestContext('', $_SERVER['REQUEST_METHOD']));

$generator = new UrlGenerator($collection, new RequestContext());

//$pathInfo = $_SERVER['PATH_INFO'] ?? '/';

$pathInfo = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';

Controller::setTwig($generator);
