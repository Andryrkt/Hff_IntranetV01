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