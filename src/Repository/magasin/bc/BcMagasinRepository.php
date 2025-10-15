<?php

namespace App\Repository\magasin\bc;

use Doctrine\ORM\EntityRepository;

class BcMagasinRepository extends EntityRepository
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
}