<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitDevisSoumisAValidationRepository extends EntityRepository
{
    public function findNumeroVersionMax($numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }
}