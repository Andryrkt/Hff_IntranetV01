<?php

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;



require_once __DIR__ . '/config/dotenv.php';
require __DIR__ . '/config/bootstrap.php';
require __DIR__ . '/config/listeConstructeur.php';



try {
    $curentRoute = $matcher->match($request->getPathInfo());
    $request->attributes->add($curentRoute);
    
    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);
   
    call_user_func_array($controller, $arguments);
} catch (ResourceNotFoundException $e) {
    $htmlContent = $twig->render('404.html.twig');
    $response->setContent($htmlContent);
    $response->setStatusCode(404);
} catch (AccessDeniedException $e) {
    $htmlContent = $twig->render('403.html.twig');
    $response->setContent($htmlContent);
    $response->setStatusCode(403);
}
// catch (Exception $e) {
//     $htmlContent = "<html><body><h1>500</h1><p>Une erreur s'est produite.</p></body></html>";
//     $response->setContent($htmlContent);
//     $response->setStatusCode(500);
// }

$response->send();
