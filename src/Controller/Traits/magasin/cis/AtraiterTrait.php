<?php

namespace App\Controller\Traits\magasin\cis;

use App\Entity\admin\utilisateur\User;
use App\Service\TableauEnStringService;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\cis\CisATraiterModel;

trait AtraiterTrait
{
    private function recupData($criteria)
    {
         $cisATraiterModel = new CisATraiterModel();

        $ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $numORItvValides = TableauEnStringService::orEnString($ditOrsSoumisRepository->findNumOrItvValide());

        $data = $cisATraiterModel->listOrATraiter($criteria, $numORItvValides);

        return $data;
    }
    
    private function agenceUser($autoriser): ?string
    {
        $codeAgence = $this->getUser()->getAgenceAutoriserCode();

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
        $userId = $this->getSessionService()->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds) || in_array(6, $roleIds);
    }
}
