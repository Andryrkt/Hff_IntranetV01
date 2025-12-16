<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use App\Loader\CustomAnnotationClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\FormRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

// Autoload et variables d'environnement
require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

require_once __DIR__ . '/listeConstructeur.php';

// Variables pour CLI
$_ENV['BASE_PATH_COURT'] ??= '/Hffintranet';
$_SERVER['HTTP_HOST'] ??= 'localhost';
$_SERVER['REQUEST_URI'] ??= '/';

// Répertoire cache
$cacheDir = dirname(__DIR__) . '/var/cache';
@mkdir($cacheDir, 0777, true);

// --- Chargement du container depuis cache ou build ---
$containerFile = $cacheDir . '/container.bin';
if (!file_exists($containerFile)) {

    $container = new ContainerBuilder();
    $container->setParameter('kernel.project_dir', dirname(__DIR__));
    $container->setParameter('kernel.cache_dir', $cacheDir);
    $container->setParameter('kernel.debug', true);

    // Doctrine
    $entityManager = require dirname(__DIR__) . '/doctrineBootstrap.php';
    $container->set('doctrine.orm.default_entity_manager', $entityManager);

    // Services de base
    $container->set('request_stack', new RequestStack());
    $container->set('session', new Session(new NativeSessionStorage()));

    // Charger services.yaml et parameters.yaml
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.yaml');
    $loader->load('parameters.yaml');

    // Twig
    $twig = new Environment(
        new FilesystemLoader([
            dirname(__DIR__) . '/Views/templates',
            dirname(__DIR__) . '/vendor/symfony/twig-bridge/Resources/views/Form',
        ]),
        ['debug' => true, 'cache' => $cacheDir . '/twig']
    );
    $container->set('twig', $twig);

    // --- Services Twig et Navigation ---
    $session = $container->get('session');
    $requestStack = $container->get('request_stack');

    $menuService = new \App\Service\navigation\MenuService($session);
    $breadcrumbService = new \App\Service\navigation\BreadcrumbMenuService($menuService);

    $twig->addExtension(new DebugExtension());
    $twig->addExtension(new TranslationExtension(new \Symfony\Component\Translation\Translator('fr_FR')));
    $twig->addExtension(new RoutingExtension(new \Symfony\Component\Routing\Generator\UrlGenerator(new RouteCollection(), new RequestContext())));
    $twig->addExtension(new FormExtension());
    $twig->addExtension(new \App\Twig\AppExtension($session, $requestStack));
    $twig->addExtension(new \App\Twig\BreadcrumbExtension($breadcrumbService));
    $twig->addExtension(new \App\Twig\CarbonExtension());
    $twig->addExtension(new \App\Twig\DeleteWordExtension());

    // Asset
    $publicPath = $_ENV['BASE_PATH_COURT'] . '/public';
    $packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));
    $twig->addExtension(new AssetExtension($packages));

    // FormRendererEngine
    $defaultFormTheme = 'bootstrap_5_layout.html.twig';
    $formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
    $twig->addRuntimeLoader(new FactoryRuntimeLoader([
        FormRenderer::class => fn() => new FormRenderer($formEngine),
    ]));

    // FormFactory
    $formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
        ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
        ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(\Symfony\Component\Validator\Validation::createValidator()))
        ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
        ->addExtension(new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($container->get('doctrine')))
        ->getFormFactory();
    $container->set('form.factory', $formFactory);

    // Pagination
    Paginator::useBootstrap();

    // Routes via Annotation + cache
    $routeCacheFile = $cacheDir . '/routes.bin';
    $routeCache = new ConfigCache($routeCacheFile, false);

    if (!$routeCache->isFresh()) {
        $collection = new RouteCollection();
        $reader = new AnnotationReader();
        foreach ([dirname(__DIR__) . '/src/Controller', dirname(__DIR__) . '/src/Api'] as $dir) {
            if (!is_dir($dir)) continue;

            $loader = new AnnotationDirectoryLoader(
                new FileLocator($dir),
                new CustomAnnotationClassLoader($reader)
            );
            $sub = $loader->load($dir);
            $collection->addCollection($sub);

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $collection->addResource(new FileResource($file->getPathname()));
                }
            }
        }

        $routeCache->write(serialize($collection), $collection->getResources());
    } else {
        $collection = unserialize(file_get_contents($routeCacheFile));
    }

    foreach ($collection as $route) {
        $route->setOption('case_sensitive', false);
    }
    $container->set('route_collection', $collection);

    // Compiler container
    $container->compile();

    // Sauvegarder container
    file_put_contents($containerFile, serialize($container));
} else {
    $container = unserialize(file_get_contents($containerFile));
}

// --- Correction préfixe /Hffintranet/
$request = Request::createFromGlobals();
$pathInfo = $request->getPathInfo();
if (stripos($pathInfo, '/hffintranet') === 0 && strpos($pathInfo, '/Hffintranet') !== 0) {
    $correctUrl = preg_replace('#^/hffintranet#i', '/Hffintranet', $pathInfo);
    (new \Symfony\Component\HttpFoundation\RedirectResponse($correctUrl, 301))->send();
    exit;
}
$container->get('request_stack')->push($request);

// Context + matcher + resolvers
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($container->get('route_collection'), $context);

$controllerResolver = new ContainerControllerResolver($container);
$argumentResolver = new ArgumentResolver();

global $container;

return [
    'twig'               => $container->get('twig'),
    'matcher'            => $matcher,
    'argumentResolver'   => $argumentResolver,
    'controllerResolver' => $controllerResolver,
];
