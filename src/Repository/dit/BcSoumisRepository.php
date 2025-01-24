<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class BcSoumisRepository extends EntityRepository {
    public function findNumeroVersionMax($numBc)
    {
        $numeroVersionMax = $this->createQueryBuilder('bc')
            ->select('MAX(bc.numVersion)')
            ->where('bc.numBc = :numBc')
            ->setParameter('numBc', $numBc)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }
}
