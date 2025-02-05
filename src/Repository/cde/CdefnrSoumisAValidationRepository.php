<?php

namespace App\Repository\cde;

use Doctrine\ORM\EntityRepository;

class CdefnrSoumisAValidationRepository extends EntityRepository 
{
    public function findNumeroVersionMax($numCde)
    {
        $numeroVersionMax = $this->createQueryBuilder('cde')
            ->select('MAX(cde.numVersion)')
            ->where('cde.numCdeFournisseur = :numCdeFournisseur')
            ->setParameter('numCdeFournisseur', $numCde)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }
}