<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitOrsSoumisAValidationRepository extends EntityRepository
{
    public function findNumeroVersionMax()
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }
}