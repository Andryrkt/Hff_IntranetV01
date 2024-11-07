<?php

namespace App\Controller\Traits\magasin\cis;

use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitOrsSoumisAValidation;

trait AtraiterTrait
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

    private function orEnString($tab): string
    {
        $numOrValide = $this->transformEnSeulTableau($tab);

        return implode("','", $numOrValide);
    }

    public function transformEnSeulTableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as  $values) {
            if(is_array($values)){
                foreach ($values as $value) {
                    $tab[] = $value;
                }
            } else {
                $tab[] = $values;
            }
            
        }

        return $tab;
    }
}