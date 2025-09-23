<?php

namespace App\Security\Voter;

use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Voter pour gérer les autorisations d'accès aux applications
 */
class ApplicationVoter extends Voter
{
    // Constantes pour les attributs supportés
    public const ACCESS = 'ACCESS';
    public const VIEW = 'VIEW';
    public const CREATE = 'CREATE';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    // Constantes pour les applications
    public const DOM = 'DOM';
    public const TIK = 'TIK';
    public const MAGASIN = 'MAGASIN';
    public const INVENTAIRE = 'INVENTAIRE';

    protected function supports(string $attribute, $subject): bool
    {
        // Vérifier si l'attribut est supporté
        if (!in_array($attribute, [self::ACCESS, self::VIEW, self::CREATE, self::EDIT, self::DELETE])) {
            return false;
        }

        // Vérifier si le sujet est supporté (Application ou string)
        return $subject instanceof Application || is_string($subject) || is_int($subject);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user instanceof User) {
            return false;
        }

        // Vérifier si l'utilisateur a une session valide
        if (!$this->hasValidSession($user)) {
            throw new AccessDeniedException('Session utilisateur invalide ou expirée');
        }

        // Déterminer l'ID de l'application
        $applicationId = $this->getApplicationId($subject);

        // Vérifier les autorisations selon l'attribut
        switch ($attribute) {
            case self::ACCESS:
                return $this->canAccess($user, $applicationId);
            case self::VIEW:
                return $this->canView($user, $applicationId);
            case self::CREATE:
                return $this->canCreate($user, $applicationId);
            case self::EDIT:
                return $this->canEdit($user, $applicationId);
            case self::DELETE:
                return $this->canDelete($user, $applicationId);
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur a une session valide
     */
    private function hasValidSession(User $user): bool
    {
        // Ici vous pouvez ajouter votre logique de vérification de session
        // Par exemple, vérifier si la session n'est pas expirée
        return $user->getId() !== null;
    }

    /**
     * Détermine l'ID de l'application à partir du sujet
     */
    private function getApplicationId($subject): int
    {
        if ($subject instanceof Application) {
            return $subject->getId();
        }

        if (is_string($subject)) {
            return $this->getApplicationIdFromString($subject);
        }

        if (is_int($subject)) {
            return $subject;
        }

        throw new \InvalidArgumentException('Type de sujet non supporté pour ApplicationVoter');
    }

    /**
     * Convertit une chaîne d'application en ID
     */
    private function getApplicationIdFromString(string $application): int
    {
        $mapping = [
            self::DOM => Application::ID_DOM,
            self::TIK => 2, // Ajustez selon vos constantes
            self::MAGASIN => 3,
            self::INVENTAIRE => 4,
        ];

        if (!isset($mapping[$application])) {
            throw new \InvalidArgumentException("Application non reconnue: $application");
        }

        return $mapping[$application];
    }

    /**
     * Vérifie si l'utilisateur peut accéder à l'application
     */
    private function canAccess(User $user, int $applicationId): bool
    {
        // Vérifier si l'utilisateur a les autorisations pour cette application
        $authorizedApplications = $this->getUserAuthorizedApplications($user);

        return in_array($applicationId, $authorizedApplications);
    }

    /**
     * Vérifie si l'utilisateur peut voir les données de l'application
     */
    private function canView(User $user, int $applicationId): bool
    {
        return $this->canAccess($user, $applicationId);
    }

    /**
     * Vérifie si l'utilisateur peut créer des données dans l'application
     */
    private function canCreate(User $user, int $applicationId): bool
    {
        // Logique spécifique pour la création
        // Par exemple, vérifier des rôles spécifiques
        return $this->canAccess($user, $applicationId) && $this->hasCreatePermission($user);
    }

    /**
     * Vérifie si l'utilisateur peut modifier des données dans l'application
     */
    private function canEdit(User $user, int $applicationId): bool
    {
        // Logique spécifique pour la modification
        return $this->canAccess($user, $applicationId) && $this->hasEditPermission($user);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer des données dans l'application
     */
    private function canDelete(User $user, int $applicationId): bool
    {
        // Logique spécifique pour la suppression
        return $this->canAccess($user, $applicationId) && $this->hasDeletePermission($user);
    }

    /**
     * Récupère les applications autorisées pour l'utilisateur
     */
    private function getUserAuthorizedApplications(User $user): array
    {
        // Ici vous implémentez votre logique d'autorisation
        // Par exemple, récupérer depuis la base de données ou les propriétés de l'utilisateur

        $authorizedApplications = [];

        // Exemple basique - à adapter selon votre logique métier
        if ($user->getAgenceAutoriserCode()) {
            $authorizedApplications[] = Application::ID_DOM;
        }

        if ($user->getServiceAutoriserCode()) {
            $authorizedApplications[] = Application::ID_TIK ?? 2;
        }

        // Ajouter d'autres applications selon vos règles métier
        // ...

        return $authorizedApplications;
    }

    /**
     * Vérifie si l'utilisateur a la permission de création
     */
    private function hasCreatePermission(User $user): bool
    {
        // Logique pour vérifier les permissions de création
        // Par exemple, vérifier des rôles spécifiques
        $roleIds = $user->getRoleIds();
        return in_array(1, $roleIds) || in_array(2, $roleIds); // Ajustez selon vos rôles
    }

    /**
     * Vérifie si l'utilisateur a la permission de modification
     */
    private function hasEditPermission(User $user): bool
    {
        // Logique pour vérifier les permissions de modification
        $roleIds = $user->getRoleIds();
        return in_array(1, $roleIds) || in_array(3, $roleIds); // Ajustez selon vos rôles
    }

    /**
     * Vérifie si l'utilisateur a la permission de suppression
     */
    private function hasDeletePermission(User $user): bool
    {
        // Logique pour vérifier les permissions de suppression
        $roleIds = $user->getRoleIds();
        return in_array(1, $roleIds); // Seuls les administrateurs peuvent supprimer
    }
}
