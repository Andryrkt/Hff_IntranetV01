<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Contrôleur de base qui étend ControllerDI avec des méthodes helper communes
 * Utilise l'injection de dépendances pour tous les services
 */
abstract class BaseController extends Controller
{
    /**
     * Méthode helper pour le rendu Twig avec Response
     */
    public function render(string $template, array $context = []): Response
    {
        $content = $this->getTwig()->render($template, $context);
        return new Response($content);
    }

    /**
     * Méthode helper pour la redirection vers une route avec Response
     */
    protected function redirectToRouteResponse(string $routeName, array $params = []): RedirectResponse
    {
        $url = $this->getUrlGenerator()->generate($routeName, $params);
        return new RedirectResponse($url);
    }

    /**
     * Méthode helper pour la redirection vers une URL avec Response
     */
    protected function redirectToResponse(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

    /**
     * Méthode helper pour créer une réponse JSON
     */
    protected function jsonResponse($data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Méthode helper pour vérifier si l'utilisateur est connecté
     */
    public function isUserConnected(): bool
    {
        return $this->getSessionService()->has('user_id');
    }

    /**
     * Méthode helper pour obtenir l'ID de l'utilisateur connecté
     */
    protected function getCurrentUserId()
    {
        return $this->getSessionService()->get('user_id');
    }

    /**
     * Méthode helper pour obtenir le nom de l'utilisateur connecté
     */
    protected function getCurrentUsername()
    {
        return $this->getSessionService()->get('user');
    }
}
