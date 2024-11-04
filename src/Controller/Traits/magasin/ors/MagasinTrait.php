<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinTrait
{
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

    private function firstDateOfWeek()
    {
        $today = new \DateTime();
        $dayOfWeek = $today->format('N');
        $daysToMonday = $dayOfWeek - 1;
        return $today->modify("-$daysToMonday days");
    }

    private function recupNumOrSelonCond(array $criteria): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        $numOrValideString = $this->orEnString($magasinModel->recupNumOr($criteria));
        $numOrEncours = $this->orEnString($this->magasinListOrEncoursModel->recupOrEncours());

        return  [
            "numOrEncours" => $numOrEncours,
            "numOrValideString" => $numOrValideString
        ];
    }

}