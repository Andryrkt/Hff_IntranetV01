<?php

namespace App\Factory\magasin\devis;

use App\Dto\Magasin\Devis\PointageRelanceDto;
use App\Entity\magasin\devis\PointageRelance;

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

    public function map(array $data, string $userName): PointageRelance
    {
        $entity = new PointageRelance();
        $entity->setNumeroDevis($data['numeroDevis']);
        $entity->setDateDeRelance(new \DateTime($data['dateDeRelance']));
        $entity->setUtilisateur($userName);
        $entity->setAgence('01');
        $entity->setSociete('HF');
        return $entity;
    }
}
