<?php

namespace App\Controller\Traits\magasin\cis;

use App\Entity\admin\utilisateur\User;

trait AtraiterTrait
{
    private function agenceUser($em): ?string
    {
        $agenceServiceUser = $this->agenceServiceIpsObjet();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($em);
        //FIN AUTORISATION

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