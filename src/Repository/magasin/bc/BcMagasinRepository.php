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
            ->select('b.statutBc')
            ->where('b.numeroDevis = :identifier')
            ->orderBy('b.id', 'DESC')
            ->setParameter('identifier', $identifier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['statutBc'] ?? null;
    }

    /**
     * recupère tous les numéros BC Distincts
     */
    public function findnumBCAll()
    {
        $query = $this->createQueryBuilder('b')
            ->select("DISTINCT b.numeroDevis")
            ->where('b.statutBc = :statutBc')
            ->setParameter('statutBc', 'Validé - Devis à transferer')
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }
}
