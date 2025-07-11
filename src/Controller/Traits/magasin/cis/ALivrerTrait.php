<?php

namespace App\Controller\Traits\magasin\cis;

use App\Service\TableauEnStringService;
use App\Entity\admin\utilisateur\User;

trait ALivrerTrait
{
    private function agenceUser($autoriser): ?string
    {
        $codeAgence = Controller::getUser()->getAgenceAutoriserCode();

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        return $agenceUser;
    }

    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->sessionService->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds) || in_array(6, $roleIds);
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
            if (is_array($values)) {
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
