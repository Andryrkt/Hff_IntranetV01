<?php

// Configuration pour l'affichage détaillé des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/var/log/php_errors.log');

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// Vérifier et régénérer les proxies Doctrine si nécessaire
$proxyDir = __DIR__ . '/var/cache/proxies';
if (!is_dir($proxyDir) || empty(glob($proxyDir . '/__CG__*.php'))) {
    // Charger l'EntityManager pour régénérer les proxies
    include __DIR__ . "/doctrineBootstrap.php";

    // Vérifier que l'EntityManager a été créé
    if (!isset($entityManager) || !$entityManager instanceof \Doctrine\ORM\EntityManagerInterface) {
        throw new \RuntimeException("Failed to create EntityManager for proxy generation");
    }

    // Créer le dossier proxies s'il n'existe pas
    if (!is_dir($proxyDir)) {
        mkdir($proxyDir, 0755, true);
    }

    // Régénérer les proxies
    $proxyFactory = $entityManager->getProxyFactory();
    $proxyFactory->generateProxyClasses($entityManager->getMetadataFactory()->getAllMetadata());
}

// Charger le bootstrap DI
$services = require __DIR__ . '/config/bootstrap_di.php';

// Récupérer les services nécessaires
$container = $services['container'];
$matcher = $services['matcher'];
$controllerResolver = $services['controllerResolver'];
$argumentResolver = $services['argumentResolver'];
$twig = $services['twig'];
$response = new \Symfony\Component\HttpFoundation\Response();

// Créer la requête depuis les variables globales
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

try {
    // Matcher la route
    $currentRoute = $matcher->match($request->getPathInfo());
    $request->attributes->add($currentRoute);

    // Résoudre le contrôleur
    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    // Exécuter le contrôleur
    $result = call_user_func_array($controller, $arguments);

    // Si le contrôleur retourne une Response, l'utiliser
    if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
        $response = $result;
    } else {
        // Sinon, essayer de rendre le résultat avec Twig
        if (is_string($result)) {
            $response->setContent($result);
        }
    }
} catch (ResourceNotFoundException $e) {
    // Route non trouvée
    $htmlContent = $twig->render('erreur/404.html.twig');
    $response->setContent($htmlContent);
    $response->setStatusCode(404);
} catch (AccessDeniedException $e) {
    // Accès refusé
    $htmlContent = $twig->render('erreur/403.html.twig');
    $response->setContent($htmlContent);
    $response->setStatusCode(403);
} catch (Exception $e) {
    // Erreur générale - Affichage détaillé pour le débogage
    $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'code' => $e->getCode(),
        'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
    ];

    // En mode développement, afficher les détails complets
    if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'dev') {
        echo "<h1>Erreur 500 - Détails de l'erreur</h1>";
        echo "<h2>Message d'erreur:</h2>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<h2>Fichier et ligne:</h2>";
        echo "<pre>" . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . "</pre>";
        echo "<h2>Stack trace:</h2>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "<h2>Code d'erreur:</h2>";
        echo "<pre>" . $e->getCode() . "</pre>";
        if ($e->getPrevious()) {
            echo "<h2>Erreur précédente:</h2>";
            echo "<pre>" . htmlspecialchars($e->getPrevious()->getMessage()) . "</pre>";
        }
        exit;
    } else {
        // En mode production, utiliser le template
        $htmlContent = $twig->render('erreur/500.html.twig', $errorDetails);
        $response->setContent($htmlContent);
        $response->setStatusCode(500);
    }
}

// Envoyer la réponse
$response->send();
