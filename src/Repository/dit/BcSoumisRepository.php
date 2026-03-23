<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class BcSoumisRepository extends EntityRepository
{
    public function findNumeroVersionMax($numBc, $codeSociete)
    {
        $numeroVersionMax = $this->createQueryBuilder('bc')
            ->select('MAX(bc.numVersion)')
            ->where('bc.numBc = :numBc')
            ->andWhere('bc.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete)
            ->setParameter('numBc', $numBc)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    public function findNumeroVersionMaxParDit($numDIT, $codeSociete)
    {
        $numeroVersionMax = $this->createQueryBuilder('bc')
            ->select('MAX(bc.numVersion)')
            ->where('bc.numDit = :numDit')
            ->andWhere('bc.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete)
            ->setParameter('numDit', $numDIT)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }
}
