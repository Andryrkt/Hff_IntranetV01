<?php

namespace App\Factory\magasin\devis\Pointage;

use App\Dto\Magasin\Devis\Pointage\EnvoyerAuClientDto;

class EnvoyerAuClientFactory
{
    public function create(string $numeroDevis): EnvoyerAuClientDto
    {
        $dto = new EnvoyerAuClientDto();
        $dto->numeroDevis = $numeroDevis;

        return $dto;
    }
}
