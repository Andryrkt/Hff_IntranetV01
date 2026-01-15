<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

require dirname(__DIR__) . '/vendor/autoload.php';

// ========================================
// 🔥 CHARGER L'ENVIRONNEMENT
// ========================================
if (file_exists(dirname(__DIR__) . '/.env')) \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

$isDevMode = ($_ENV['APP_ENV'] ?? 'prod') === 'dev'; // par défaut en prod

// ========================================
// 🔥 CHARGER LE CONTENEUR
// ========================================
$containerFile = dirname(__DIR__) . '/var/cache/Container.php';

if (!file_exists($containerFile)) dd("Le conteneur n'existe pas.", "Exécutez d'abord : php config/bootstrap_build.php");

require $containerFile;
/** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
$container = new AppContainer();

// ========================================
// 🔥 CHARGER LES ROUTES (DEV vs PROD)
// ========================================

$routeCacheFile = dirname(__DIR__) . '/var/cache/routes.php';
$cacheRoutes = new ConfigCache($routeCacheFile, $isDevMode); // Mode DEV = Mode debug = vérification auto des fichiers

if (!$cacheRoutes->isFresh()) {
    // EN DEV : Recompilation automatique si fichiers modifiés
    // EN PROD : Ne devrait jamais arriver (sauf si cache supprimé)

    $collection = new \Symfony\Component\Routing\RouteCollection();
    $reader = new \Doctrine\Common\Annotations\AnnotationReader();
    foreach ([dirname(__DIR__) . '/src/Controller', dirname(__DIR__) . '/src/Api'] as $dir) {
        if (!is_dir($dir)) continue;
        $loaderAnnotation = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader(
            new \Symfony\Component\Config\FileLocator($dir),
            new \App\Loader\CustomAnnotationClassLoader($reader)
        );
        $sub = $loaderAnnotation->load($dir);
        $collection->addCollection($sub);

        // Ajouter les ressources pour détection de changements
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $collection->addResource(new \Symfony\Component\Config\Resource\FileResource($file->getPathname()));
            }
        }
    }

    foreach ($collection as $route) {
        $route->setOption('case_sensitive', false);
    }

    // Écriture du cache avec toutes les ressources
    $cacheRoutes->write(serialize($collection), $collection->getResources());

    if ($isDevMode) error_log("🔄 Routes recompilées automatiquement (mode dev)");
} else {
    // Charger la collection depuis le cache
    $collection = unserialize(file_get_contents($routeCacheFile));
}

// ========================================
// 🔥 CHARGER TWIG (DEV vs PROD)
// ========================================

$twigCacheDir = dirname(__DIR__) . '/var/cache/twig';
@mkdir($twigCacheDir, 0777, true);

$twig = new \Twig\Environment(
    new \Twig\Loader\FilesystemLoader([
        dirname(__DIR__) . '/Views/templates',
        dirname(__DIR__) . '/vendor/symfony/twig-bridge/Resources/views/Form',
    ]),
    [
        'debug'       => $isDevMode,
        'cache'       => $twigCacheDir,
        'auto_reload' => $isDevMode, // 🔥 EN DEV : vérifie les changements
    ]
);
$container->set('twig', $twig);

// ========================================
// 🔥 SESSION & SERVICES RUNTIME
// ========================================

$session = new \Symfony\Component\HttpFoundation\Session\Session(
    new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage()
);
$container->set('session', $session);

$formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
    ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
    ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(\Symfony\Component\Validator\Validation::createValidator()))
    ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
    ->addExtension(new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($container->get('doctrine')))
    ->addExtension(new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension($container, [], []))
    ->getFormFactory();

$container->set('form.factory', $formFactory);

// ========================================
// 🔥 VARIABLES D'ENVIRONNEMENT
// ========================================

require_once __DIR__ . '/listeConstructeur.php';

$_ENV['BASE_PATH_COURT'] ??= '/Hffintranet';
$_SERVER['HTTP_HOST'] ??= 'localhost';
$_SERVER['REQUEST_URI'] ??= '/';

// ========================================
// 🔥 REQUEST & ROUTING
// ========================================

$request = Request::createFromGlobals();
$container->get('request_stack')->push($request);

// --- Correction casse /Hffintranet/ ---
$pathInfo = $request->getPathInfo();
if (stripos($pathInfo, '/hffintranet') === 0 && strpos($pathInfo, '/Hffintranet') !== 0) {
    $correctUrl = preg_replace('#^/hffintranet#i', '/Hffintranet', $pathInfo);
    (new \Symfony\Component\HttpFoundation\RedirectResponse($correctUrl, 301))->send();
    exit;
}

// --- UrlGenerator / Matcher ---
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($collection, $context);
$urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);
$container->set('router', $urlGenerator);

// ========================================
// 🔥 EXTENSIONS TWIG
// ========================================

// --- Twig extensions runtime (Menuservice) ---
$menuService = new \App\Service\navigation\MenuService($session);
$container->set('menuService', $menuService);

// --- Twig extensions runtime ---
$twig = $container->get('twig');
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension(new \Symfony\Component\Translation\Translator('fr_FR')));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\RoutingExtension($urlGenerator));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension());
$twig->addExtension(new \App\Twig\AppExtension($session, $container->get('request_stack')));
$twig->addExtension(new \App\Twig\BreadcrumbExtension(new \App\Service\navigation\BreadcrumbMenuService($menuService)));
$twig->addExtension(new \App\Twig\CarbonExtension());
$twig->addExtension(new \App\Twig\DeleteWordExtension());

// --- Asset Extension ---
$publicPath = $_ENV['BASE_PATH_COURT'] . '/public';
$packages = new \Symfony\Component\Asset\Packages(
    new \Symfony\Component\Asset\PathPackage($publicPath, new \Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy())
);
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\AssetExtension($packages));

// --- FormRendererEngine ---
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
$formEngine = new \Symfony\Bridge\Twig\Form\TwigRendererEngine([$defaultFormTheme], $twig);
$twig->addRuntimeLoader(new \Twig\RuntimeLoader\FactoryRuntimeLoader([
    \Symfony\Component\Form\FormRenderer::class => fn() => new \Symfony\Component\Form\FormRenderer($formEngine),
]));

// ========================================
// 🔥 CONTROLLERS / RESOLVERS
// ========================================
$controllerResolver = new ContainerControllerResolver($container);
$argumentResolver = new ArgumentResolver();

global $container;

return [
    'twig'               => $twig,
    'matcher'            => $matcher,
    'controllerResolver' => $controllerResolver,
    'argumentResolver'   => $argumentResolver,
];
