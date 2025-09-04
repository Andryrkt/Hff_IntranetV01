<?php

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        } else {
            $response->setContent("Contrôleur exécuté avec succès");
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
    // Erreur générale
    $htmlContent = "<html><body><h1>500</h1><p>Une erreur s'est produite : " . $e->getMessage() . "</p></body></html>";
    $response->setContent($htmlContent);
    $response->setStatusCode(500);
}

// Envoyer la réponse
$response->send();
