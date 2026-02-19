<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;

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

// ─── Cache applicatif (inter-requêtes, partagé par profil) ───────────────────
// FilesystemTagAwareAdapter : stockage fichier + support des tags (invalidation par profil).
// En DEV : TTL court (60s) pour voir les changements rapidement.
// En PROD : TTL 1h, invalidation explicite via DataService::invaliderCacheProfil().
$cachePermissions = new FilesystemTagAwareAdapter(
    'security',                                         // namespace : sous-dossier dans var/cache/pools/
    $isDevMode ? 60 : 3600,                             // defaultLifetime : DEV=1min, PROD=1h
    dirname(__DIR__) . '/var/cache/pools'               // directory : séparé du cache Twig/routes
);
$container->set('cache.permissions', $cachePermissions);

// ─── DataService : source de vérité du contexte utilisateur ──────────────────
// Gère session + BDD + cache applicatif.
// Les permissions/pages sont calculées une fois par profil puis mises en cache.
// Les entités (Profil, etc.) sont rechargées 1 fois par requête HTTP (cache mémoire).
$dataService = new \App\Service\UserData\UserDataService(
    $container->get('doctrine.orm.default_entity_manager'),
    $session,
    $cachePermissions
);
$container->set('data.service', $dataService);

// ─── SecurityService : contrôle d'accès (délègue tout à DataService) ─────────
$securityService = new \App\Service\security\SecurityService($dataService);
$container->set('security.service', $securityService);

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

// ─── Cache applicatif (inter-requêtes, partagé par profil) ───────────────────
$cacheMenu = new FilesystemTagAwareAdapter(
    'menu',
    $isDevMode ? 60 : 3600,
    dirname(__DIR__) . '/var/cache/pools'
);

// MenuService reçoit dataService pour filtrer les items via les routes (zéro BDD)
$menuService = new \App\Service\navigation\MenuService($dataService, $cacheMenu);
$container->set('menuService', $menuService);

// --- Twig extensions runtime ---
$twig = $container->get('twig');
$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension(new \Symfony\Component\Translation\Translator('fr_FR')));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\RoutingExtension($urlGenerator));
$twig->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension());
$twig->addExtension(new \App\Twig\AppExtension($session, $container->get('request_stack')));
$twig->addExtension(new \App\Twig\BreadcrumbExtension($menuService, $securityService));
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
// 🔥 PRÉCOMPILATION TWIG (PROD uniquement)
// ========================================

if (!$isDevMode) {
    // Fichier marqueur pour savoir si la précompilation a déjà été faite
    $twigCompiledMarker = $twigCacheDir . '/.compiled';

    if (!file_exists($twigCompiledMarker)) {
        // Première exécution en PROD : précompiler tous les templates
        $templateDir = dirname(__DIR__) . '/Views/templates';

        if (is_dir($templateDir)) {
            // Normaliser le chemin pour comparaison
            $templateDir = str_replace('\\', '/', realpath($templateDir));

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($templateDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $compiledCount = 0;
            $templateError = [];

            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;

                $extension = $file->getExtension();

                // Ne compiler que les fichiers .twig
                if ($extension !== 'twig') continue;

                // Normaliser le chemin du fichier
                $filePath = str_replace('\\', '/', $file->getPathname());

                // Calculer le nom relatif du template
                $templateName = str_replace($templateDir . '/', '', $filePath);

                try {
                    // Charger le template pour forcer la compilation
                    $twig->load($templateName);
                    $compiledCount++;
                } catch (\Twig\Error\LoaderError $e) {
                    // Template non trouvé (peut arriver avec des fichiers cachés)
                    $templateError[] = "  ⚠️  LoaderError {$templateName}: {$e->getMessage()}";
                } catch (\Twig\Error\SyntaxError $e) {
                    // Erreur de syntaxe Twig
                    $templateError[] = "  ❌ SyntaxError {$templateName}: {$e->getMessage()}";
                } catch (\Twig\Error\RuntimeError $e) {
                    // Erreur d'exécution (ex: variable manquante)
                    // C'est normal, on compile juste la structure
                    $compiledCount++;
                } catch (\Exception $e) {
                    // Autre erreur
                    $templateError[] = "  ❌ Exception {$templateName}: {$e->getMessage()}";
                }
            }

            $errorCount = count($templateError);
            // Créer le fichier marqueur avec statistiques
            $stats = [
                'compiled_at'        => date('Y-m-d H:i:s'),
                'env'                => $_ENV['APP_ENV'] ?? 'prod',
                'templates_compiled' => $compiledCount,
                'templates_errors'   => $errorCount,
            ];
            file_put_contents($twigCompiledMarker, json_encode($stats, JSON_PRETTY_PRINT) . PHP_EOL);
            foreach ($templateError as $error) {
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
$argumentResolver = new ArgumentResolver();

global $container;

return [
    'twig'               => $twig,
    'matcher'            => $matcher,
    'securityService'    => $securityService,
    'controllerResolver' => $controllerResolver,
    'argumentResolver'   => $argumentResolver,
];
