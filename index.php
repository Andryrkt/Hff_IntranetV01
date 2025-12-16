<?php
require __DIR__ . '/src/Utils/PerfLogger.php';

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Yaml\Yaml;
use App\Utils\PerfLogger;

$perfLogger = PerfLogger::getInstance();
$perfLogger->log('Démarrage du script', 'index.php');

// Charger le bootstrap DI
$services = require __DIR__ . '/config/bootstrap.php';
$perfLogger->log("\$services = require __DIR__ . '/config/bootstrap.php'", 'index.php');

// Récupérer les services nécessaires
$twig               = $services['twig'];
$matcher            = $services['matcher'];
$argumentResolver   = $services['argumentResolver'];
$controllerResolver = $services['controllerResolver'];
$perfLogger->log("assignation des services", 'index.php');
$response           = new \Symfony\Component\HttpFoundation\Response();
$perfLogger->log("assignation de la response new \Symfony\Component\HttpFoundation\Response()", 'index.php');

// Créer la requête depuis les variables globales
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$perfLogger->log("assignation de la request \Symfony\Component\HttpFoundation\Request::createFromGlobals()", 'index.php');

try {
    // Matcher la route
    $currentRoute = $matcher->match($request->getPathInfo());
    $perfLogger->log("\$currentRoute = \$matcher->match(\$request->getPathInfo());", 'index.php');
    $request->attributes->add($currentRoute);
    $perfLogger->log("\$request->attributes->add(\$currentRoute);", 'index.php');

    // Résoudre le contrôleur
    $controller = $controllerResolver->getController($request);
    $perfLogger->log("\$controller = \$controllerResolver->getController(\$request);", 'index.php');
    $arguments = $argumentResolver->getArguments($request, $controller);
    $perfLogger->log("\$arguments = \$argumentResolver->getArguments(\$request, \$controller);", 'index.php');

    // Exécuter le contrôleur
    $result = call_user_func_array($controller, $arguments);
    $perfLogger->log("\$result = call_user_func_array(\$controller, \$arguments);", 'index.php');

    // Si le contrôleur retourne une Response, l'utiliser
    if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
        $response = $result;
    } else {
        // Sinon, essayer de rendre le résultat avec Twig
        if (is_string($result)) {
            $response->setContent($result);
        }
    }
    $perfLogger->log("Obtention de Response", 'index.php');
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
        'message'        => $e->getMessage(),
        'file'           => $e->getFile(),
        'line'           => $e->getLine(),
        'trace'          => $e->getTraceAsString(),
        'code'           => $e->getCode(),
        'previous'       => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
        'timestamp'      => date('Y-m-d H:i:s'),
        'request_uri'    => $request->getRequestUri(),
        'request_method' => $request->getMethod(),
        'user_agent'     => $request->headers->get('User-Agent'),
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
            'message'   => 'Une erreur interne est survenue. Veuillez contacter l\'administrateur.',
            'error_id'  => uniqid('ERR_', true),
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
$perfLogger->log("\$response->send();", 'index.php');

$perfLogger->log("Fin du script", 'index.php');
$perfLogger->save();
