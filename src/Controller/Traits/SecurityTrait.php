<?php

namespace App\Controller\Traits;

use App\Service\security\SecurityService;
use App\Security\Voter\ApplicationVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Trait pour simplifier l'utilisation du SecurityService dans les contrôleurs
 */
trait SecurityTrait
{
    private $securityService;

    /**
     * Vérifie l'accès à une application
     * 
     * @param string $application Nom de l'application (DOM, TIK, etc.)
     * @param string $attribute Attribut d'autorisation (ACCESS, CREATE, EDIT, DELETE)
     * @throws AccessDeniedException Si l'accès est refusé
     */
    protected function requireAccess(string $application, string $attribute = ApplicationVoter::ACCESS): void
    {
        $this->securityService->verifyUserAccess($this->getUser(), $application, $attribute);
    }

    /**
     * Vérifie si l'utilisateur peut accéder à une application
     * 
     * @param string $application Nom de l'application
     * @param string $attribute Attribut d'autorisation
     * @return bool
     */
    protected function canAccess(string $application, string $attribute = ApplicationVoter::ACCESS): bool
    {
        return $this->securityService->isGranted($application, $attribute);
    }

    /**
     * Vérifie si l'utilisateur peut créer dans une application
     */
    protected function canCreate(string $application): bool
    {
        return $this->securityService->canCreate($application);
    }

    /**
     * Vérifie si l'utilisateur peut modifier dans une application
     */
    protected function canEdit(string $application): bool
    {
        return $this->securityService->canEdit($application);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer dans une application
     */
    protected function canDelete(string $application): bool
    {
        return $this->securityService->canDelete($application);
    }

    /**
     * Vérifie l'accès et lève une exception si refusé
     * 
     * @param string $application Nom de l'application
     * @param string $attribute Attribut d'autorisation
     * @throws AccessDeniedException Si l'accès est refusé
     */
    protected function requireCreate(string $application): void
    {
        $this->requireAccess($application, ApplicationVoter::CREATE);
    }

    /**
     * Vérifie l'accès en modification et lève une exception si refusé
     */
    protected function requireEdit(string $application): void
    {
        $this->requireAccess($application, ApplicationVoter::EDIT);
    }

    /**
     * Vérifie l'accès en suppression et lève une exception si refusé
     */
    protected function requireDelete(string $application): void
    {
        $this->requireAccess($application, ApplicationVoter::DELETE);
    }
}
