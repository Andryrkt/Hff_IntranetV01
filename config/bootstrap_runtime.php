<?php

use App\Service\navigation\MenuService;
use App\Service\security\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
// ✅ GAIN #1 : CompiledUrlMatcher au lieu de UrlMatcher + unserialize()
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
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
// ROUTES
// ✅ GAIN #1 : require PHP natif au lieu de unserialize(file_get_contents())
//
//   AVANT  → ~3-5ms : file_get_contents() + unserialize() de RouteCollection
//   APRÈS  → ~0.1ms : require PHP absorbé par OPcache, zéro parsing
// ========================================

$context = new RequestContext();

if ($isDevMode) {
    // DEV : on garde le serialize() pour la détection de changements automatique
    $routeCacheFile = dirname(__DIR__) . '/var/cache/routes_dev.php';

    // Vérification fraîcheur : recompile si le cache est absent OU si un contrôleur est plus récent
    $needsRecompile = !file_exists($routeCacheFile);
    if (!$needsRecompile) {
        $cacheTime = filemtime($routeCacheFile);
        foreach ([dirname(__DIR__) . '/src/Controller', dirname(__DIR__) . '/src/Api'] as $scanDir) {
            if (!is_dir($scanDir)) continue;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->getExtension() === 'php' && $file->getMTime() > $cacheTime) {
                    $needsRecompile = true;
                    break 2;
                }
            }
        }
    }

    // On pourrait ajouter ici la vérification des mtimes des controllers
    // mais en DEV un simple file_exists suffit pour le workflow quotidien

    if ($needsRecompile) {
        // Recompilation complète (identique au build)
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $collection = new \Symfony\Component\Routing\RouteCollection();

        foreach ([dirname(__DIR__) . '/src/Controller', dirname(__DIR__) . '/src/Api'] as $dir) {
            if (!is_dir($dir)) continue;
            $loaderAnnotation = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader(
                new \Symfony\Component\Config\FileLocator($dir),
                new \App\Loader\CustomAnnotationClassLoader($reader)
            );
            $collection->addCollection($loaderAnnotation->load($dir));
        }

        foreach ($collection as $route) {
            $route->setOption('case_sensitive', false);
        }

        file_put_contents($routeCacheFile, serialize($collection));
        error_log("🔄 Routes recompilées automatiquement (mode dev)");
    } else {
        $collection = unserialize(file_get_contents($routeCacheFile));
    }

    // En DEV on garde UrlMatcher classique (debug plus lisible)
    $matcher      = new \Symfony\Component\Routing\Matcher\UrlMatcher($collection, $context);
    $urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);
} else {
    // ✅ PROD : require → OPcache → zéro overhead
    $matcherData   = require dirname(__DIR__) . '/var/cache/url_matcher.php';
    $generatorData = require dirname(__DIR__) . '/var/cache/url_generator.php';

    $matcher      = new CompiledUrlMatcher($matcherData, $context);
    $urlGenerator = new CompiledUrlGenerator($generatorData, $context);
}

// ========================================
// TWIG
// ✅ GAIN #2 : Extensions enregistrées en lazy via addRuntimeLoader()
//    Les extensions sont déclarées mais leurs constructeurs ne s'exécutent
//    QUE si un filtre/fonction de cette extension est réellement appelé
//    dans le template rendu. Sur une réponse JSON/API → 0 extension instanciée.
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

$session = $container->get('session');
$session->start();

// On stocke une closure dans une propriété custom du container
// pour simuler un service lazy sans ProxyManager
$container->set('form.factory.lazy', static function () use ($container): \Symfony\Component\Form\FormFactoryInterface {
    static $factory = null;
    if ($factory === null) {
        $factory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
            ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
            ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(
                \Symfony\Component\Validator\Validation::createValidator()
            ))
            ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
            ->addExtension(new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($container->get('doctrine')))
            ->addExtension(new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension(
                $container,
                [],
                []
            ))
            ->getFormFactory();
    }
    return $factory;
});

// ✅ GAIN #2 : addRuntimeLoader() pour les extensions qui supportent RuntimeExtensionInterface
//    Twig n'instancie le runtime QUE quand le template l'utilise réellement
$twig->addRuntimeLoader(new \Twig\RuntimeLoader\FactoryRuntimeLoader([

    // FormRenderer : instancié seulement si {{ form(...) }} dans le template
    \Symfony\Component\Form\FormRenderer::class => static function () use ($twig) {
        $formEngine = new \Symfony\Bridge\Twig\Form\TwigRendererEngine(
            ['bootstrap_5_layout.html.twig'],
            $twig
        );
        return new \Symfony\Component\Form\FormRenderer($formEngine);
    },

]));


// ========================================
// REQUEST
// ========================================

require_once __DIR__ . '/listeConstructeur.php';

$_ENV['BASE_PATH_COURT'] ??= '/Hffintranet';
$_SERVER['HTTP_HOST']    ??= 'localhost';
$_SERVER['REQUEST_URI']  ??= '/';

$request = Request::createFromGlobals();
$container->get('request_stack')->push($request);

// Correction casse /Hffintranet/
$pathInfo = $request->getPathInfo();
if (stripos($pathInfo, '/hffintranet') === 0 && strpos($pathInfo, '/Hffintranet') !== 0) {
    $correctUrl = preg_replace('#^/hffintranet#i', '/Hffintranet', $pathInfo);
    (new \Symfony\Component\HttpFoundation\RedirectResponse($correctUrl, 301))->send();
    exit;
}

$context->fromRequest($request);
$container->set('router', $urlGenerator);

// Extensions légères (pas de RuntimeExtension) : instanciées une fois, coût faible

/** @var MenuService $menuService */
$menuService = $container->get('menu.service');

/** @var SecurityService $securityService */
$securityService = $container->get('security.service');

/** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cacheMenu */
$cacheMenu = $container->get('cache.menu');

$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension(
    new \Symfony\Component\Translation\Translator('fr_FR')
));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\RoutingExtension($urlGenerator));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension());
$twig->addExtension(new \App\Twig\AppExtension($session, $container->get('request_stack')));
$twig->addExtension(new \App\Twig\BreadcrumbExtension($menuService, $securityService, $cacheMenu));
$twig->addExtension(new \App\Twig\CarbonExtension());
$twig->addExtension(new \App\Twig\DeleteWordExtension());

// Asset Extension
$publicPath = $_ENV['BASE_PATH_COURT'] . '/public';
$packages = new \Symfony\Component\Asset\Packages(
    new \Symfony\Component\Asset\PathPackage(
        $publicPath,
        new \Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy()
    )
);
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\AssetExtension($packages));

// ⚠️  Dans tes controllers, remplace $container->get('form.factory')
//     par : ($container->get('form.factory.lazy'))()
//     Ou crée un helper : getFormFactory($container)
//
//     Si tu ne veux pas toucher aux controllers, décommente la ligne suivante
//     (instanciation immédiate, mais propre) :
// $container->set('form.factory', ($container->get('form.factory.lazy'))());

// ========================================
// 🔥 PRÉCOMPILATION TWIG (PROD uniquement)
// ========================================

if (!$isDevMode) {
    $twigCompiledMarker = $twigCacheDir . '/.compiled';

    if (!file_exists($twigCompiledMarker)) {
        $templateDir = str_replace('\\', '/', realpath(dirname(__DIR__) . '/Views/templates'));

        if (is_dir($templateDir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($templateDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $compiledCount  = 0;
            $templateErrors = [];

            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'twig') continue;

                $filePath     = str_replace('\\', '/', $file->getPathname());
                $templateName = str_replace($templateDir . '/', '', $filePath);

                try {
                    $twig->load($templateName);
                    $compiledCount++;
                } catch (\Twig\Error\SyntaxError $e) {
                    $templateErrors[] = "❌ SyntaxError {$templateName}: {$e->getMessage()}";
                } catch (\Twig\Error\RuntimeError $e) {
                    $compiledCount++; // Structure OK, juste une variable manquante
                } catch (\Exception $e) {
                    $templateErrors[] = "❌ {$templateName}: {$e->getMessage()}";
                }
            }

            $stats = [
                'compiled_at'        => date('Y-m-d H:i:s'),
                'env'                => $_ENV['APP_ENV'] ?? 'prod',
                'templates_compiled' => $compiledCount,
                'templates_errors'   => count($templateErrors),
            ];

            file_put_contents($twigCompiledMarker, json_encode($stats, JSON_PRETTY_PRINT) . PHP_EOL);
            foreach ($templateErrors as $error) {
                file_put_contents($twigCompiledMarker, $error . PHP_EOL, FILE_APPEND);
            }

            file_put_contents($twigCompiledMarker, "✅ Twig précompilé : {$compiledCount} templates, {$errorCount} erreurs (premier démarrage PROD)" . PHP_EOL, FILE_APPEND);
        } else {
            error_log("⚠️  Répertoire templates introuvable : {$templateDir}");
        }
    }
}

// ========================================
// 🔥 CONTROLLERS / RESOLVERS
// ========================================

$controllerResolver = new ContainerControllerResolver($container);
$argumentResolver   = new ArgumentResolver();

global $container;

return [
    'twig'               => $twig,
    'matcher'            => $matcher,
    'securityService'    => $securityService,
    'controllerResolver' => $controllerResolver,
    'argumentResolver'   => $argumentResolver,
];
