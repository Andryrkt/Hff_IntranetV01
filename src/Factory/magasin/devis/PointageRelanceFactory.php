<?php

namespace App\Factory\magasin\devis;

use App\Dto\Magasin\Devis\PointageRelanceDto;
use App\Entity\magasin\devis\PointageRelance;
use App\Model\magasin\devis\Pointage\PointageRelanceModel;
use App\Service\autres\VersionService;

class PointageRelanceFactory
{
    public function create(?string $numeroDevis): PointageRelanceDto
    {
        $dto = new PointageRelanceDto();
        $dto->dateDeRelance = new \DateTimeImmutable();
        $dto->dateDePointage = new \DateTimeImmutable();
        $dto->numeroDevis = $numeroDevis;

        return $dto;
    }

    public function map(array $data, string $userName, int $numeroRelance, string $codeSociete): PointageRelance
    {
        $entity = new PointageRelance();
        $entity->setNumeroDevis($data['numeroDevis']);
        $entity->setDateDeRelance(new \DateTime($data['dateDeRelance']));
        $entity->setUtilisateur($userName);
        $entity->setAgence('01');
        $entity->setNumeroRelance($numeroRelance);
        $entity->setCodeSociete($codeSociete);
        $pointageRelanceModel = new PointageRelanceModel();
        $numeroVersionPointageRelance = VersionService::autoIncrement($pointageRelanceModel->getNumeroVersionPointageRelance($data['numeroDevis'], $codeSociete));
        $entity->setNumeroVersion($numeroVersionPointageRelance);

        return $entity;
    }
}
