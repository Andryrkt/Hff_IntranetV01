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

    public function getStatut(string $numDit, ?string $numeroDevis = null, string $codeSociete): ?string
    {
        if ($numeroDevis === null) {
            return null;
        }

        $result = $this->createQueryBuilder('bc')
            ->select('bc.statut')
            ->where('bc.numDit = :numDit')
            ->andWhere('bc.numDevis = :numDevis')
            ->andWhere('bc.codeSociete = :codeSociete')
            ->setParameter('numDit', $numDit)
            ->setParameter('numDevis', $numeroDevis)
            ->setParameter('codeSociete', $codeSociete)
            ->setMaxResults(1)
            ->orderBy('bc.numVersion', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['statut'] : null;
    }
}
