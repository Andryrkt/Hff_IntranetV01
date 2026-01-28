<?php

namespace App\Factory\magasin\devis;

use App\Dto\Magasin\Devis\PointageRelanceDto;

class PointageRelanceFactory
{
    public function create(string $numeroDevis): PointageRelanceDto
    {
        $dto = new PointageRelanceDto();
        $dto->dateDeRelance = new \DateTimeImmutable();
        $dto->dateDePointage = new \DateTimeImmutable();
        $dto->numeroDevis = $numeroDevis;

        return $dto;
    }
}
