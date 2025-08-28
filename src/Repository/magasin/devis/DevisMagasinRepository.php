<?php

namespace App\Repository\magasin\devis;

use Doctrine\ORM\EntityRepository;

class DevisMagasinRepository extends EntityRepository
{
    public function findNumeroVersionMax(string $numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$numeroVersionMax;
    }
}
