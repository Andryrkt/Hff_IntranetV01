<?php

namespace App\Repository\magasin\devis;

use Doctrine\ORM\EntityRepository;

class PointageRelanceRepository extends EntityRepository
{
    public function findDernierDateDeRelance(string $numeroDevis, string $codeSociete): ?string
    {
        $result = $this->createQueryBuilder('pr')
            ->select('pr.dateDeRelance')
            ->where('pr.numeroDevis = :numeroDevis')
            ->andWhere('pr.codeSociete = :codeSociete')
            ->orderBy('pr.dateDeRelance', 'DESC')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setParameter('codeSociete', $codeSociete)
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result[0]['dateDeRelance'] ?? null;
    }

    public function findNumeroRelance(string $numeroDevis, string $codeSociete): ?int
    {
        $count = $this->createQueryBuilder('pr')
            ->select('pr.numeroRelance')
            ->where('pr.numeroDevis = :numeroDevis')
            ->andWhere('pr.codeSociete = :codeSociete')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setParameter('codeSociete', $codeSociete)
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return is_numeric($count[0]['numeroRelance'] ?? null) ? (int)$count[0]['numeroRelance'] : null;
    }

    public function getNumeroRelanceMax(int $numeroDevis, string $codeSociete): ?int
    {
        $numeroVersionMax = $this->createQueryBuilder('pr')
            ->select('pr.numeroRelance')
            ->where('pr.numeroDevis = :numDevis')
            ->andWhere('pr.codeSociete = :codeSociete')
            ->setParameter('numDevis', $numeroDevis)
            ->setParameter('codeSociete', $codeSociete)
            ->orderBy('pr.numeroRelance', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $numeroVersionMax[0]['numeroRelance'] ?? null;
    }
}
