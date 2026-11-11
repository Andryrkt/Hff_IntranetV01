<?php

use Symfony\Component\Yaml\Yaml;
use App\Service\security\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// Chargement du bootstrap runtime (adaptatif)
$services = require __DIR__ . '/config/bootstrap_runtime.php';

// Récupérer les services nécessaires
$twig               = $services['twig'];
$matcher            = $services['matcher'];
$argumentResolver   = $services['argumentResolver'];
$controllerResolver = $services['controllerResolver'];
/** @var SecurityService $securityService */
$securityService    = $services['securityService'];
$response           = new Response();

// Même objet que le request_stack : les attributs de route (_route...) sont ainsi visibles de Twig (App.request) et des services
$request = $services['request'];

try {
    // 1. Matcher la route (utilise l'objet Request complet pour gérer les méthodes HTTP, etc.)
    $currentRoute = $matcher->matchRequest($request);
    $request->attributes->add($currentRoute);

    // 2. Contrôler l'accès
    $securityResponse = $securityService->controlerAcces($request);

    if ($securityResponse !== null) {
        // L'utilisateur n'est pas connecté : on le redirige vers le login
        $securityResponse->send();
        exit;
    }

    // 3. Résoudre le contrôleur
    $controller = $controllerResolver->getController($request);
    $arguments  = $argumentResolver->getArguments($request, $controller);

    // 4. Exécuter le contrôleur
    $result     = call_user_func_array($controller, $arguments);

    // 5. Construire la réponse
    if ($result instanceof Response) {
        $response = $result;
    } elseif (is_string($result)) {
        $response->setContent($result);
    }
} catch (ResourceNotFoundException $e) {
    // Route non trouvée
    $htmlContent = $twig->render('erreur/404.html.twig');
    $response->setContent($htmlContent);
    $response->setStatusCode(404);
} catch (\Symfony\Component\Routing\Exception\MethodNotAllowedException $e) {
    // Méthode HTTP non autorisée
    $allowedMethods = $e->getAllowedMethods();
    $response->setContent(json_encode([
        'success' => false,
        'message' => 'Méthode HTTP non autorisée. Méthodes autorisées : ' . implode(', ', $allowedMethods),
        'method' => $request->getMethod(),
        'allowed' => $allowedMethods
    ]));
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode(405);
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

    $htmlContent = $twig->render('erreur/500.html.twig', $errorDetails);
    $response->setContent($htmlContent);
    $response->setStatusCode(500);

    // Logger l'erreur complète
    error_log("Erreur 500 - " . json_encode($errorDetails));
}

// Historique des consultations des pages HTML qui ne l'enregistrent pas elles-mêmes, fait après l'envoi :
// la taille annoncée permet au navigateur de terminer la page sans attendre la fin du script
$userInfo = $session->get('user_info');
$journaliser = \App\Service\historiqueOperation\JournalConsultation::doitEnregistrer($request, $response, $userInfo);
// (seulement si toute la page est dans la réponse : rien d'affiché directement par un echo du contrôleur)
if ($journaliser && is_string($response->getContent()) && $response->getContent() !== '' && !headers_sent() && !ob_get_length()) {
    $response->headers->set('Content-Length', (string) strlen($response->getContent()));
    $response->headers->set('Content-Encoding', 'none');
    $response->headers->set('Connection', 'close');
}

// Envoyer la réponse
$response->send();

if ($journaliser) {
    session_write_close(); // libère la session pour la page suivante
    \App\Service\historiqueOperation\JournalConsultation::enregistrer($container->get('doctrine')->getConnection(), $request, $response, $userInfo);
}
