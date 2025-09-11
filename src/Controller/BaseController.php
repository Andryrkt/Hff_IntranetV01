<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\SessionManagerService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Contrôleur de base avec injection de dépendances Symfony
 */
abstract class BaseController
{
    protected EntityManagerInterface $entityManager;
    protected FormFactoryInterface $formFactory;
    protected SessionManagerService $sessionService;
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected UrlGeneratorInterface $urlGenerator;
    protected SessionInterface $session;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        SessionManagerService $sessionService,
        AuthorizationCheckerInterface $authorizationChecker,
        UrlGeneratorInterface $urlGenerator,
        SessionInterface $session
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->sessionService = $sessionService;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
    }

    /**
     * Récupère l'EntityManager
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Récupère le FormFactory
     */
    protected function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * Récupère le SessionService
     */
    protected function getSessionService(): SessionManagerService
    {
        return $this->sessionService;
    }

    /**
     * Récupère l'utilisateur connecté
     */
    protected function getUser()
    {
        return $this->session->get('user');
    }

    /**
     * Vérifie la session utilisateur
     */
    protected function verifierSessionUtilisateur(): void
    {
        // Implémentation de la vérification de session
        if (!$this->getUser()) {
            throw new \RuntimeException('Utilisateur non connecté');
        }
    }

    /**
     * Vérifie l'autorisation d'accès
     */
    private function autorisationAcces($user, $applicationId): void
    {
        // Implémentation de la vérification d'autorisation
        // Cette méthode peut être surchargée dans les classes filles
    }

    /**
     * Rendu d'une vue Twig
     */
    protected function render(string $view, array $parameters = []): string
    {
        // Implémentation basique du rendu
        // Cette méthode peut être étendue selon les besoins
        return "Rendu de la vue : $view";
    }
}
