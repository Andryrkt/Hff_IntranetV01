<?php
$start = microtime(true);

function log_perf($label, $startTime)
{
    static $previous = null;
    static $i = 0;
    global $perf_logs;
    $i++;
    $now = microtime(true);
    $perf_logs[] = [
        'index' => sprintf("%03d", $i),
        'label' => $label,
        'temps' => sprintf("%.5fs", $previous ? $now - $previous : $now - $startTime),
        'total' => sprintf("%.5fs", $now - $startTime),
    ];
    $previous = $now;
}

function save_perf_logs($filename = 'perf_logs.json')
{
    global $perf_logs;
    file_put_contents($filename, json_encode($perf_logs, JSON_PRETTY_PRINT));
}

log_perf('Commencement dans `bootstrap_di.php`', $start);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use App\Loader\CustomAnnotationClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

log_perf('Use - Importation des classes', $start);
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

log_perf('Vendor autoload chargé', $start);
// Charger les variables d'environnement
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

log_perf('Chargement du fichier .env', $start);

// Charger les variables globales
require_once __DIR__ . '/listeConstructeur.php';

log_perf('Chargement du fichier .env', $start);

// Définir les variables d'environnement manquantes pour les tests CLI
if (!isset($_ENV['BASE_PATH_COURT'])) {
    $_ENV['BASE_PATH_COURT'] = '/Hffintranet';
}
log_perf('if (!isset($_ENV[\'BASE_PATH_COURT\'])) { ...', $start);
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
log_perf('if (!isset($_SERVER[\'HTTP_HOST\'])) { ...', $start);
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}
log_perf('if (!isset($_SERVER[\'REQUEST_URI\'])) { ...', $start);
// Configuration pour les tests CLI (session gérée dans test_di.php)

// Créer le conteneur de services
$container = new ContainerBuilder();
log_perf('Création du conteneur de services -> new ContainerBuilder()', $start);
// Ajouter les paramètres de base manquants
$container->setParameter('kernel.project_dir', dirname(__DIR__));
log_perf('kernel.project_dir dans $container', $start);
$container->setParameter('kernel.cache_dir', dirname(__DIR__) . '/var/cache');
log_perf('kernel.cache_dir dans $container', $start);
$container->setParameter('kernel.debug', true);
log_perf('kernel.debug dans $container', $start);

// Créer l'EntityManager manuellement AVANT de charger la configuration
$entityManager = require_once dirname(__DIR__) . "/doctrineBootstrap.php";
log_perf('$entityManager = require_once dirname(__DIR__) . "/doctrineBootstrap.php"', $start);

// Créer le ManagerRegistry pour Doctrine
$registry = new \core\SimpleManagerRegistry($entityManager);
log_perf('$registry = new \core\SimpleManagerRegistry($entityManager)', $start);

// Enregistrer l'EntityManager comme service AVANT de charger la configuration
$container->set('doctrine.orm.default_entity_manager', $entityManager);
log_perf('$container->set(\'doctrine.orm.default_entity_manager\', $entityManager)', $start);
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
log_perf('$loader = new YamlFileLoader($container, new FileLocator(__DIR__))', $start);
$loader->load('services.yaml');
log_perf('$loader->load(\'services.yaml\')', $start);

// Créer les services de base manuellement
$container->register('twig', 'Twig\Environment')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'twig\', \'Twig\Environment\')', $start);

$container->register('form.factory', 'Symfony\Component\Form\FormFactory')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'form.factory\', \'Symfony\Component\Form\FormFactory\')', $start);

$container->register('router', 'Symfony\Component\Routing\Router')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'router\', \'Symfony\Component\Routing\Router\')', $start);

$container->register('session', 'Symfony\Component\HttpFoundation\Session\Session')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'session\', \'Symfony\Component\HttpFoundation\Session\Session\')', $start);

$container->register('request_stack', 'Symfony\Component\HttpFoundation\RequestStack')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'request_stack\', \'Symfony\Component\HttpFoundation\RequestStack\')', $start);

$container->register('security.token_storage', 'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'security.token_storage\', \'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage\')', $start);

$container->register('security.authorization_checker', 'Symfony\Component\Security\Core\Authorization\AuthorizationChecker')
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'security.authorization_checker\', \'Symfony\Component\Security\Core\Authorization\AuthorizationChecker\')', $start);

// Enregistrer les services Twig AVANT de charger la configuration
$container->register('App\Twig\AppExtension', \App\Twig\AppExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Twig\AppExtension\', \App\Twig\AppExtension::class)', $start);

$container->register('App\Service\navigation\MenuService', \App\Service\navigation\MenuService::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Service\navigation\MenuService\', \App\Service\navigation\MenuService::class)', $start);

$container->register('App\Service\navigation\BreadcrumbMenuService', \App\Service\navigation\BreadcrumbMenuService::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Service\navigation\BreadcrumbMenuService\', \App\Service\navigation\BreadcrumbMenuService::class)', $start);

$container->register('App\Twig\BreadcrumbExtension', \App\Twig\BreadcrumbExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Twig\BreadcrumbExtension\', \App\Twig\BreadcrumbExtension::class)', $start);

$container->register('App\Twig\CarbonExtension', \App\Twig\CarbonExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Twig\CarbonExtension\', \App\Twig\CarbonExtension::class)', $start);

$container->register('App\Twig\DeleteWordExtension', \App\Twig\DeleteWordExtension::class)
    ->setSynthetic(true)
    ->setPublic(true);
log_perf('$container->register(\'App\Twig\DeleteWordExtension\', \App\Twig\DeleteWordExtension::class)', $start);

// Charger les paramètres
$loader->load('parameters.yaml');
log_perf('$loader->load(\'parameters.yaml\');', $start);
// L'EntityManager est déjà assigné plus haut

// Compiler le conteneur
$container->compile();
log_perf('$container->compile();', $start);

// Créer les services manuellement (comme dans l'ancien bootstrap)
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([
    dirname(__DIR__) . '/Views/templates',
    dirname(__DIR__) . '/vendor/symfony/twig-bridge/Resources/views/Form',
]), ['debug' => true]);
log_perf('$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([dirname(__DIR__) . \'/Views/templates\',dirname(__DIR__) . \'/vendor/symfony/twig-bridge/Resources/views/Form\']), [\'debug\' => true]);', $start);

$container->set('twig', $twig);
log_perf('$container->set(\'twig\', $twig);', $start);

// Create the form factory with container integration
$formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
    ->addExtension(new \Symfony\Component\Form\Extension\Core\CoreExtension())
    ->addExtension(new \Symfony\Component\Form\Extension\Validator\ValidatorExtension(\Symfony\Component\Validator\Validation::createValidator()))
    ->addExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension())
    ->addExtension(new \Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($container->get('doctrine')))
    ->addExtension(new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension($container, [], []))
    ->getFormFactory();

log_perf('$formFactory = ... ', $start);

$container->set('form.factory', $formFactory);
log_perf('$container->set(\'form.factory\', $formFactory);', $start);

// Créer et assigner les autres services
$session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage());
log_perf('$session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage());', $start);
$container->set('session', $session);
log_perf('$container->set(\'session\', $session);', $start);

$requestStack = new \Symfony\Component\HttpFoundation\RequestStack();
log_perf('$requestStack = new \Symfony\Component\HttpFoundation\RequestStack();', $start);
$container->set('request_stack', $requestStack);
log_perf('$container->set(\'request_stack\', $requestStack);', $start);

$tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
log_perf('$tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();', $start);
$container->set('security.token_storage', $tokenStorage);
log_perf('$container->set(\'security.token_storage\', $tokenStorage);', $start);

$accessDecisionManager = new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager([
    new \Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy()
]);
log_perf('$accessDecisionManager = new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager(...', $start);
$authorizationChecker = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker($tokenStorage, $accessDecisionManager);
log_perf('$authorizationChecker = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker($tokenStorage, $accessDecisionManager);', $start);
$container->set('security.authorization_checker', $authorizationChecker);
log_perf('$container->set(\'security.authorization_checker\', $authorizationChecker);', $start);

// Créer et assigner le translator
$translator = new \Symfony\Component\Translation\Translator('fr_FR');
log_perf('$translator = new \Symfony\Component\Translation\Translator(\'fr_FR\');', $start);
$translator->addLoader('xlf', new \Symfony\Component\Translation\Loader\XliffFileLoader());
log_perf('$translator->addLoader(\'xlf\', new \Symfony\Component\Translation\Loader\XliffFileLoader());', $start);
$container->set('translator', $translator);
log_perf('$container->set(\'translator\', $translator);', $start);

// Récupérer les services du conteneur
$session = $container->get('session');
log_perf('$session = $container->get(\'session\');', $start);
$requestStack = $container->get('request_stack');
log_perf('$requestStack = $container->get(\'request_stack\');', $start);
$tokenStorage = $container->get('security.token_storage');
log_perf('$tokenStorage = $container->get(\'security.token_storage\');', $start);
$authorizationChecker = $container->get('security.authorization_checker');
log_perf('$authorizationChecker = $container->get(\'security.authorization_checker\');', $start);

// Créer et assigner les services Twig APRÈS la compilation
$appExtension = new \App\Twig\AppExtension($session, $requestStack, $tokenStorage, $authorizationChecker, $entityManager);
log_perf('$appExtension = new \App\Twig\AppExtension($session, $requestStack, $tokenStorage, $authorizationChecker, $entityManager);', $start);
$container->set('App\Twig\AppExtension', $appExtension);
log_perf('$container->set(\'App\Twig\AppExtension\', $appExtension);', $start);

$menuService = new \App\Service\navigation\MenuService($entityManager, $session);
log_perf('$menuService = new \App\Service\navigation\MenuService($entityManager);', $start);
$container->set('App\Service\navigation\MenuService', $menuService);
log_perf('$container->set(\'App\Service\navigation\MenuService\', $menuService);', $start);

$breadcrumbMenuService = new \App\Service\navigation\BreadcrumbMenuService($menuService);
log_perf('$breadcrumbMenuService = new \App\Service\navigation\BreadcrumbMenuService($menuService);', $start);
$container->set('App\Service\navigation\BreadcrumbMenuService', $breadcrumbMenuService);
log_perf('$container->set(\'App\Service\navigation\BreadcrumbMenuService\', $breadcrumbMenuService);', $start);

$breadcrumbExtension = new \App\Twig\BreadcrumbExtension($breadcrumbMenuService);
log_perf('$breadcrumbExtension = new \App\Twig\BreadcrumbExtension($breadcrumbMenuService);', $start);
$container->set('App\Twig\BreadcrumbExtension', $breadcrumbExtension);
log_perf('$container->set(\'App\Twig\BreadcrumbExtension\', $breadcrumbExtension);', $start);

$carbonExtension = new \App\Twig\CarbonExtension();
log_perf('$carbonExtension = new \App\Twig\CarbonExtension();', $start);
$container->set('App\Twig\CarbonExtension', $carbonExtension);
log_perf('$container->set(\'App\Twig\CarbonExtension\', $carbonExtension);', $start);

$deleteWordExtension = new \App\Twig\DeleteWordExtension();
log_perf('$deleteWordExtension = new \App\Twig\DeleteWordExtension();', $start);
$container->set('App\Twig\DeleteWordExtension', $deleteWordExtension);
log_perf('$container->set(\'App\Twig\DeleteWordExtension\', $deleteWordExtension);', $start);

// Créer la requête et la réponse
$request = Request::createFromGlobals();
log_perf('$request = Request::createFromGlobals();', $start);
$response = new Response();
log_perf('$response = new Response();', $start);

// Vérifier la casse du préfixe /Hffintranet/
$pathInfo = $request->getPathInfo();
log_perf('$pathInfo = $request->getPathInfo();', $start);
if (stripos($pathInfo, '/hffintranet') === 0 && strpos($pathInfo, '/Hffintranet') !== 0) {
    $correctUrl = preg_replace('#^/hffintranet#i', '/Hffintranet', $pathInfo);
    $redirectResponse = new \Symfony\Component\HttpFoundation\RedirectResponse($correctUrl, 301);
    $redirectResponse->send();
    exit; // on arrête le script après la redirection
}
log_perf('if (stripos($pathInfo, ...', $start);

$cacheAllRoutesFile = dirname(__DIR__) . '/var/cache/all_routes.php'; // Fichier cache commun pour routes controllers + API
log_perf('$cacheAllRoutesFile = ' . $cacheAllRoutesFile, $start);
$cacheRoutes = new ConfigCache($cacheAllRoutesFile, true); // TODO Mode debug : true => vérifie si les fichiers ont changé
log_perf('$cacheRoutes = new ConfigCache($cacheAllRoutesFile, true);', $start);

// Dossiers à charger
$dirs = [
    dirname(__DIR__) . '/src/Controller/',
    dirname(__DIR__) . '/src/Api/',
];
log_perf('$dirs = [' . $dirs[0] . ',' . $dirs[1] . ']', $start);

// Collection finale
$collection = new RouteCollection();
log_perf('$collection = new RouteCollection();', $start);

if (!$cacheRoutes->isFresh()) {
    log_perf('if (!$cacheRoutes->isFresh()) ', $start);
    $controllerCollection = new RouteCollection();
    log_perf('$controllerCollection = new RouteCollection();', $start);
    $annotationReader = new AnnotationReader();
    log_perf('$annotationReader = new AnnotationReader();', $start);

    // 🔁 Charger les routes depuis tous les dossiers
    foreach ($dirs as $dir) {
        log_perf('foreach ($dirs as $dir)', $start);
        if (!is_dir($dir)) continue;
        log_perf('if (!is_dir($dir))', $start);

        $routeLoader = new AnnotationDirectoryLoader(
            new FileLocator($dir),
            new CustomAnnotationClassLoader($annotationReader)
        );
        log_perf('$routeLoader = new AnnotationDirectoryLoader(...', $start);

        $subCollection = $routeLoader->load($dir);
        log_perf('$subCollection = $routeLoader->load($dir);', $start);
        $controllerCollection->addCollection($subCollection);
        log_perf('$controllerCollection->addCollection($subCollection);', $start);

        // Ajouter toutes les ressources à surveiller
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        log_perf('$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));', $start);
        foreach ($rii as $file) {
            log_perf('foreach ($rii as $file)', $start);
            if ($file->isFile() && $file->getExtension() === 'php') {
                log_perf('if ($file->isFile() && $file->getExtension() === \'php\')', $start);
                $controllerCollection->addResource(new FileResource($file->getPathname()));
                log_perf('$controllerCollection->addResource(new FileResource($file->getPathname()));', $start);
            }
        }
        log_perf('Fin foreach ($rii as $file)', $start);
    }
    log_perf('Fin foreach ($dirs as $dir)', $start);

    // Écriture du cache avec toutes les ressources
    $cacheRoutes->write(serialize($controllerCollection), $controllerCollection->getResources());
    log_perf('$cacheRoutes->write(serialize($controllerCollection), $controllerCollection->getResources());', $start);
} else {
    log_perf('if ($cacheRoutes->isFresh()) ', $start);
    // Charger la collection depuis le cache
    $controllerCollection = unserialize(file_get_contents($cacheAllRoutesFile));
    log_perf('$controllerCollection = unserialize(file_get_contents($cacheAllRoutesFile));', $start);
}

// ➡️ Fusion finale
$collection->addCollection($controllerCollection);
log_perf('$collection->addCollection($controllerCollection);', $start);

// ➡️ Ajoute ce bloc juste ici
foreach ($collection as $route) {
    $route->setOption('case_sensitive', false);
}
log_perf('foreach ($collection as $route) {...', $start);

// Configurer le contexte de requête
$context = new RequestContext();
log_perf('$context = new RequestContext();', $start);
$context->fromRequest($request);
log_perf('$context->fromRequest($request);', $start);

// Créer le UrlGenerator avec la vraie collection de routes
$urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);
log_perf('$urlGenerator = new \Symfony\Component\Routing\Generator\UrlGenerator($collection, $context);', $start);
$container->set('router', $urlGenerator);
log_perf('$container->set(\'router\', $urlGenerator);', $start);

// Configurer le matcher d'URL
$matcher = new UrlMatcher($collection, $context);
log_perf('$matcher = new UrlMatcher($collection, $context);', $start);

// Configurer les resolvers
$controllerResolver = new ControllerResolver();
log_perf('$controllerResolver = new ControllerResolver();', $start);
$argumentResolver = new ArgumentResolver();
log_perf('$argumentResolver = new ArgumentResolver();', $start);

// Configurer Twig avec les extensions
$twig->addExtension(new DebugExtension());
log_perf('$twig->addExtension(new DebugExtension());', $start);
$twig->addExtension(new TranslationExtension($container->get('translator')));
log_perf('$twig->addExtension(new TranslationExtension($container->get(\'translator\')));', $start);
$twig->addExtension(new RoutingExtension($urlGenerator));
log_perf('$twig->addExtension(new RoutingExtension($urlGenerator));', $start);
$twig->addExtension(new FormExtension());
log_perf('$twig->addExtension(new FormExtension());', $start);
$twig->addExtension($container->get('App\Twig\AppExtension'));
log_perf('$twig->addExtension($container->get(\'App\Twig\AppExtension\'));', $start);
$twig->addExtension($container->get('App\Twig\BreadcrumbExtension'));
log_perf('$twig->addExtension($container->get(\'App\Twig\BreadcrumbExtension\'));', $start);
$twig->addExtension($container->get('App\Twig\CarbonExtension'));
log_perf('$twig->addExtension($container->get(\'App\Twig\CarbonExtension\'));', $start);
$twig->addExtension($container->get('App\Twig\DeleteWordExtension'));
log_perf('$twig->addExtension($container->get(\'App\Twig\DeleteWordExtension\'));', $start);

// Configurer l'extension Asset
$publicPath = $_ENV['BASE_PATH_COURT'] . '/public';
log_perf('$publicPath = $_ENV[\'BASE_PATH_COURT\'] . \'/public\';', $start);
$packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));
log_perf('$packages = new Packages(new PathPackage($publicPath, new EmptyVersionStrategy()));', $start);
$twig->addExtension(new AssetExtension($packages));
log_perf('$twig->addExtension(new AssetExtension($packages));', $start);

// Configurer le moteur de rendu des formulaires
$defaultFormTheme = 'bootstrap_5_layout.html.twig';
log_perf('$defaultFormTheme = \'bootstrap_5_layout.html.twig\';', $start);
$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);
log_perf('$formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);', $start);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine) {
        return new FormRenderer($formEngine);
    },
]));
log_perf('$twig->addRuntimeLoader(new FactoryRuntimeLoader([...', $start);

// Configurer la pagination
Paginator::useBootstrap();
log_perf('Paginator::useBootstrap();', $start);

// Le contrôleur principal utilise maintenant l'injection de dépendances
// Plus besoin de configuration statique

// Stocker le conteneur dans une variable globale pour y accéder
global $container;
log_perf('global $container;', $start);

// Retourner les services principaux
save_perf_logs(__DIR__ . '/perf_logs.json');

return [
    'container'            => $container,
    'entityManager'        => $entityManager,
    'twig'                 => $twig,
    'formFactory'          => $formFactory,
    'urlGenerator'         => $urlGenerator,
    'session'              => $session,
    'requestStack'         => $requestStack,
    'tokenStorage'         => $tokenStorage,
    'authorizationChecker' => $authorizationChecker,
    'matcher'              => $matcher,
    'controllerResolver'   => $controllerResolver,
    'argumentResolver'     => $argumentResolver,
    'routeCollection'      => $collection,
];
