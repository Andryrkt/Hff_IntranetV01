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

    public function findNombreDeRelances(string $numeroDevis): int
    {
        $count = $this->createQueryBuilder('pr')
            ->select('COUNT(pr.id)')
            ->where('pr.numeroDevis = :numeroDevis')
            ->setParameter('numeroDevis', $numeroDevis)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count;
    }
}