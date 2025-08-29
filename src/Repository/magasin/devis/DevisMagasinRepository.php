<?php

namespace App\Repository\magasin\devis;

use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Repository\Interfaces\StatusRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class DevisMagasinRepository extends EntityRepository implements StatusRepositoryInterface, LatestSumOfLinesRepositoryInterface
{
    public function getNumeroVersionMax(string $numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$numeroVersionMax;
    }

    public function findLatestStatusByIdentifier(string $identifier): ?string
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.statutDw')
            ->where('d.numeroDevis = :identifier')
            ->setParameter('identifier', $identifier)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result[0]['statutDw'] ?? null;
    }

    public function findLatestSumOfLinesByIdentifier(string $identifier): ?int
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.sommeNumeroLignes')
            ->where('d.numeroDevis = :identifier')
            ->setParameter('identifier', $identifier)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        $sum = $result[0]['sommeNumeroLignes'] ?? null;

        return $sum !== null ? (int)$sum : null;
    }
}
