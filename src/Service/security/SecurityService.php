<?php

namespace App\Service\security;

use App\Entity\admin\Application;
use App\Service\SessionManagerService;
use App\Security\Voter\ApplicationVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de sécurité pour la gestion des autorisations et des sessions
 * Utilise le système de Voters de Symfony pour une meilleure intégration
 */
class SecurityService
{
    private $sessionService;
    private $authorizationChecker;
    private $entityManager;

    public function __construct(
        SessionManagerService $sessionService,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->sessionService = $sessionService;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
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
        if (!$this->isGranted($application, $attribute, $user)) {
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
    public function isGranted($application, string $attribute = ApplicationVoter::ACCESS, $user = null): bool
    {
        // Utiliser le système d'authentification de l'application au lieu de Symfony Security
        if (!$user) {
            $user = $this->getUserFromSession();
        }

        if (!$user) {
            return false;
        }

        // Vérifier les autorisations via le Voter personnalisé
        $voter = new \App\Security\Voter\ApplicationVoter();

        // Créer un token pour le voter
        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user,
            'main',
            ['ROLE_USER']
        );

        return $voter->vote($token, $application, [$attribute]) === \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface::ACCESS_GRANTED;
    }

    /**
     * Récupère l'utilisateur depuis la session de l'application
     */
    public function getUserFromSession()
    {
        $userId = $this->sessionService->get('user_id');

        if ($userId && $userId !== '-') {
            // Récupérer l'utilisateur depuis la base de données
            return $this->entityManager->getRepository('App\Entity\admin\utilisateur\User')->find($userId);
        }

        return null;
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
