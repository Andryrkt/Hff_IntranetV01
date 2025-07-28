<?php

namespace App\Service;

use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AccessControlService
{
    private EntityManagerInterface $em;

    private SessionInterface $sessionService;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session
    ) {
        $this->em = $em;
        $this->sessionService = $session;
    }

    /**
     * Récupère l'utilisateur en session si besoin.
     */
    private function getUser(): ?User
    {
        $userId = $this->sessionService->get('user_id');
        if (! $userId) {
            return null;
        }

        return $this->em->getRepository(User::class)->find($userId);
    }

    public function hasAccessApp(string $application): bool
    {
        $user = $this->getUser();
        if (! $user) {
            return false;
        }

        $apps = [];
        foreach ($user->getApplications() as $app) {
            $apps[] = $app->getCodeApp();
        }

        return in_array($application, $apps, true);
    }

    public function hasAccessRole(string $roleName): bool
    {
        $user = $this->getUser();
        if (! $user) {
            return false;
        }

        $roles = [];
        foreach ($user->getRoles() as $role) {
            $roles[] = $role->getRoleName();
        }

        return in_array($roleName, $roles, true);
    }

    public function hasAccessSociette(string $societteCode): bool
    {
        $user = $this->getUser();
        if (! $user) {
            return false;
        }

        $societtes = [];
        foreach ($user->getSociettes() as $societte) {
            $societtes[] = $societte->getCodeSociete();
        }

        return in_array($societteCode, $societtes, true);
    }
}
