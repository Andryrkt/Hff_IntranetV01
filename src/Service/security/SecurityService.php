<?php

namespace App\Service\security;

use App\Entity\admin\Application;
use App\Service\SessionManagerService;
use App\Security\Voter\ApplicationVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service de sécurité pour la gestion des autorisations et des sessions
 * Utilise le système de Voters de Symfony pour une meilleure intégration
 */
class SecurityService
{
    private $sessionService;
    private $authorizationChecker;

    public function __construct(
        SessionManagerService $sessionService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->sessionService = $sessionService;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Vérifie la session utilisateur et les autorisations d'accès
     * 
     * @param object $user L'utilisateur connecté
     * @param int|string $application L'ID de l'application ou le nom de l'application
     * @param string $attribute L'attribut d'autorisation (ACCESS, VIEW, CREATE, EDIT, DELETE)
     * @throws AccessDeniedException Si l'utilisateur n'est pas connecté ou n'a pas les autorisations
     */
    public function verifyUserAccess($user, $application, string $attribute = ApplicationVoter::ACCESS): void
    {
        // Vérification de la session utilisateur
        $this->verifyUserSession();

        // Vérification des autorisations d'accès via le Voter
        if (!$this->authorizationChecker->isGranted($attribute, $application)) {
            throw new AccessDeniedException('Accès refusé à l\'application');
        }
    }

    /**
     * Vérifie uniquement la session utilisateur
     * 
     * @throws \RuntimeException Si l'utilisateur n'est pas connecté
     */
    public function verifyUserSession(): void
    {
        if (!$this->sessionService->get('user_id')) {
            throw new \RuntimeException('Utilisateur non connecté');
        }
    }

    /**
     * Vérifie si l'utilisateur peut accéder à une application
     * 
     * @param int|string $application L'ID de l'application ou le nom de l'application
     * @param string $attribute L'attribut d'autorisation
     * @return bool
     */
    public function isGranted($application, string $attribute = ApplicationVoter::ACCESS): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $application);
    }

    /**
     * Vérifie si l'utilisateur peut voir les données d'une application
     */
    public function canView($application): bool
    {
        return $this->isGranted($application, ApplicationVoter::VIEW);
    }

    /**
     * Vérifie si l'utilisateur peut créer des données dans une application
     */
    public function canCreate($application): bool
    {
        return $this->isGranted($application, ApplicationVoter::CREATE);
    }

    /**
     * Vérifie si l'utilisateur peut modifier des données dans une application
     */
    public function canEdit($application): bool
    {
        return $this->isGranted($application, ApplicationVoter::EDIT);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer des données dans une application
     */
    public function canDelete($application): bool
    {
        return $this->isGranted($application, ApplicationVoter::DELETE);
    }

    /**
     * Obtient le service de session
     * 
     * @return SessionManagerService
     */
    public function getSessionService(): SessionManagerService
    {
        return $this->sessionService;
    }
}
