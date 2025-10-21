<?php

namespace App\Repository\magasin\bc;

use App\Repository\Interfaces\StatusRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class BcMagasinRepository extends EntityRepository implements StatusRepositoryInterface
{
    public function getNumeroVersionMax(string $numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('b')
            ->select('MAX(b.numeroVersion)')
            ->where('b.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$numeroVersionMax;
    }

    public function findLatestStatusByIdentifier(string $identifier): ?string
    {
        $result = $this->createQueryBuilder('b')
            ->select('b.status')
            ->where('b.numeroDevis = :identifier')
            ->orderBy('b.id', 'DESC')
            ->setParameter('identifier', $identifier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['status'] ?? null;
    }
}