<?php

namespace App\Factory;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FrontController
{
    private UrlMatcherInterface $urlMatcher;

    private ControllerResolverInterface $controllerResolver;

    private ArgumentResolverInterface $argumentResolver;

    private LoggerInterface $logger;

    public function __construct(
        UrlMatcherInterface $urlMatcher,
        ControllerResolverInterface $controllerResolver,
        ArgumentResolverInterface $argumentResolver,
        LoggerInterface $logger // Injectez le logger
    ) {
        $this->urlMatcher = $urlMatcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
        $this->logger = $logger;
    }

    public function handleRequest(Request $request): Response
    {
        try {
            // On "match" la route selon l'URL
            $currentRoute = $this->urlMatcher->match($request->getPathInfo());
            // On ajoute les attributs de route à la requête
            $request->attributes->add($currentRoute);

            // On récupère le contrôleur et les arguments
            $controller = $this->controllerResolver->getController($request);
            $arguments = $this->argumentResolver->getArguments($request, $controller);

            // On exécute le contrôleur
            $response = $this->ensureResponse(call_user_func_array($controller, $arguments));

            // Parfois, le contrôleur peut retourner autre chose qu'une Response
            // (un string, un tableau, etc.). On standardise :
            if (! $response instanceof Response) {
                $response = new Response((string) $response);
            }
        } catch (ResourceNotFoundException $e) {
            // 404
            $this->logger->warning('Resource not found: ' . $e->getMessage());
            $response = new Response('Not Found', 404);
        } catch (AccessDeniedException $e) {
            // 403
            $this->logger->warning('Access denied: ' . $e->getMessage());
            $response = new Response('Forbidden', 403);
        }
        // catch (\Throwable $e) {
        //     // 500
        //     $this->logger->error('Internal server error: ' . $e->getMessage());
        //     $response = new Response('Internal Server Error', 500);
        // }

        return $response;
    }

    private function ensureResponse($controllerResult): Response
    {
        if ($controllerResult instanceof Response) {
            return $controllerResult;
        }

        return new Response((string) $controllerResult);
    }
}
