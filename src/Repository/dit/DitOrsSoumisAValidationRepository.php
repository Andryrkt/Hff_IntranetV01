<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitOrsSoumisAValidationRepository extends EntityRepository
{
    public function findNumeroVersionMax($numOr)
    {
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }

    public function findOrSoumiAvant($numOr)
{
    $qb = $this->createQueryBuilder('osv');

    $subquery = $this->createQueryBuilder('osv2')
        ->select('MAX(osv2.numeroVersion)')
        ->where('osv2.numeroOR = :numOr')
        ->getDQL();

    $orSoumisAvant = $qb
        ->where('osv.numeroOR = :numOr')
        ->setParameter('numOr', $numOr)
        ->andWhere($qb->expr()->eq('osv.numeroVersion', '(' . $subquery . ')'))
        ->getQuery()
        ->getResult();

    return $orSoumisAvant;
}

}