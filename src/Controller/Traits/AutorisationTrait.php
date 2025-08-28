<?php

namespace App\Controller\Traits;

use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait AutorisationTrait
{
    private function autorisationApp(User $user, int $idApp, int $idServ = 0): bool
    {
        $AppIds = $user->getApplicationsIds();
        $idServAutoriser = $user->getServiceAutoriserIds();
        $roleIds = $user->getRoleIds();

        if (in_array(Role::ROLE_ADMINISTRATEUR, $roleIds)) {
            return true;
        }

        if ($idServ === 0) {
            return in_array($idApp, $AppIds);
        }

        return in_array($idApp, $AppIds) && in_array($idServ, $idServAutoriser);
    }

    private function autorisationAcces(User $user, int $idApp, int $idServ = 0)
    {
        if (!$this->autorisationApp($user, $idApp, $idServ)) {
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
