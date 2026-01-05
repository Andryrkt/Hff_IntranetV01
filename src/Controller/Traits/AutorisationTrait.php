<?php

namespace App\Controller\Traits;

use App\Entity\admin\utilisateur\Role;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait AutorisationTrait
{
    private function autorisationApp(int $idApp, int $idServ = 0): bool
    {
        $userInfo = $this->getSessionService()->get('user_info');
        if (!$userInfo) return false; // si l'utilisateur n'est pas connecté

        if ($this->hasRoles(Role::ROLE_ADMINISTRATEUR)) return true; // si l'utilisateur est administrateur

        $hasAppAccess = in_array($idApp, $userInfo['applications'] ?? []);

        if ($idServ === 0) return $hasAppAccess;

        $hasServAccess = in_array($idServ, $userInfo['authorized_services']['ids'] ?? []);

        return $hasAppAccess && $hasServAccess;
    }

    private function autorisationAcces(int $idApp, int $idServ = 0)
    {
        if (!$this->autorisationApp($idApp, $idServ)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Vérifie si l'accès à une page ou une ressource est autorisé. Cette fonction lève une exception AccessDeniedException si l'utilisateur n'a pas les droits nécessaires pour accéder à la page.
     *
     * @param bool $hasAccess Indique si l'utilisateur a le droit d'accès.
     * @throws AccessDeniedException Si l'accès est refusé.
     * 
     * @return void
     */
    private function checkPageAccess(bool $hasAccess): void
    {
        if (!$hasAccess) {
            throw new AccessDeniedException();
        }
    }
}
