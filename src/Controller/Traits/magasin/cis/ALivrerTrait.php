<?php

namespace App\Controller\Traits\magasin\cis;

use App\Service\TableauEnStringService;
use App\Model\magasin\cis\CisALivrerModel;
use App\Entity\dit\DitOrsSoumisAValidation;

trait ALivrerTrait
{
    private function recupData($criteria)
    {
        $cisALivrerModel = new CisALivrerModel();
        $ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $numORItvValides = TableauEnStringService::orEnString($ditOrsSoumisRepository->findNumOrItvValide());
        $data = $cisALivrerModel->listOrALivrer($criteria, $numORItvValides);

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
}
