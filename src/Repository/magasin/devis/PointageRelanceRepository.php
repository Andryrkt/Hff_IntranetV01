<?php

namespace App\Repository\magasin\devis;

use Doctrine\ORM\EntityRepository;

class PointageRelanceRepository extends EntityRepository
{
    public function findDernierDateDeRelance(string $numeroDevis): ?string
    {
        $result = $this->createQueryBuilder('pr')
            ->select('pr.dateDeRelance')
            ->where('pr.numeroDevis = :numeroDevis')
            ->orderBy('pr.dateDeRelance', 'DESC')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result[0]['dateDeRelance'] ?? null;
    }

    public function findNumeroRelance(string $numeroDevis): ?int
    {
        $count = $this->createQueryBuilder('pr')
            ->select('pr.numeroRelance')
            ->where('pr.numeroDevis = :numeroDevis')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return is_numeric($count[0]['numeroRelance'] ?? null) ? (int)$count[0]['numeroRelance'] : null;
    }

    public function getNumeroRelanceMax(int $numeroDevis): ?int
    {
        $numeroVersionMax = $this->createQueryBuilder('pr')
            ->select('pr.numeroRelance')
            ->where('pr.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numeroDevis)
            ->orderBy('pr.numeroRelance', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $numeroVersionMax;
    }
}
