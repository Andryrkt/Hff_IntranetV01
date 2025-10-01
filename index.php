<?php

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Yaml\Yaml;

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
    // Erreur générale - Ajouter plus de détails
    $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'code' => $e->getCode(),
        'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_uri' => $request->getRequestUri(),
        'request_method' => $request->getMethod(),
        'user_agent' => $request->headers->get('User-Agent'),
    ];

    // Charger la configuration d'environnement
    $envConfig = Yaml::parseFile(__DIR__ . '/config/environment.yaml');
    $isDevMode = $envConfig['app']['env'] === 'dev';

    // En mode développement, afficher tous les détails
    if ($isDevMode) {
        $htmlContent = $twig->render('erreur/500.html.twig', $errorDetails);
    } else {
        // En production, masquer les détails sensibles
        $htmlContent = $twig->render('erreur/500.html.twig', [
            'message' => 'Une erreur interne est survenue. Veuillez contacter l\'administrateur.',
            'error_id' => uniqid('ERR_', true),
            'timestamp' => $errorDetails['timestamp']
        ]);
    }

    $response->setContent($htmlContent);
    $response->setStatusCode(500);

    // Logger l'erreur complète
    error_log("Erreur 500 - " . json_encode($errorDetails));
}

// Envoyer la réponse
$response->send();
