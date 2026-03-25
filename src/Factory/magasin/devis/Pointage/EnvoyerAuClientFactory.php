<?php

namespace App\Factory\magasin\devis\Pointage;

use App\Constants\Magasin\Devis\StatutBcNegConstant;
use App\Constants\Magasin\Devis\StatutDevisNegContant;
use App\Dto\Magasin\Devis\Pointage\EnvoyerAuClientDto;
use App\Model\magasin\devis\Soumission\SoumissionModel;

class EnvoyerAuClientFactory
{
    public function create(string $numeroDevis, string $codeSociete): EnvoyerAuClientDto
    {
        $dto = new EnvoyerAuClientDto();
        $dto->numeroDevis = $numeroDevis;
        $dto->codeSociete = $codeSociete;

        return $dto;
    }

    public function createFromDto(EnvoyerAuClientDto $dto): EnvoyerAuClientDto
    {
        $devisNegModel = new SoumissionModel();

        $dto->statutDw = StatutDevisNegContant::ENVOYER_CLIENT;
        $dto->statutBc = StatutBcNegConstant::EN_ATTENTE_BC;
        $dto->numeroVersion = $devisNegModel->getNumeroVersion($dto->numeroDevis);
        $dto->datePointage = new \DateTime();

        return $dto;
    }
}
