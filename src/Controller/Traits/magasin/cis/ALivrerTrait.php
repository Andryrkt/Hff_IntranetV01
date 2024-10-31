<?php

namespace App\Controller\Traits\magasin\cis;

use App\Entity\admin\utilisateur\User;

trait ALivrerTrait
{
    private function agenceUser($autoriser): ?string
    {
        $agenceServiceUser = $this->agenceServiceIpsObjet();

        if($autoriser)
        {
            $agenceUser = null;
        } else {
            $agenceUser = $agenceServiceUser['agenceIps']->getCodeAgence() .'-'.$agenceServiceUser['agenceIps']->getLibelleAgence();
        }

        return $agenceUser;
    }

    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->sessionService->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds);
    }
}
