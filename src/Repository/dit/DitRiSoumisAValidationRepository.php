<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitRiSoumisAValidationRepository extends EntityRepository
{
    public function findRiSoumis($numOr, $numDit)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('rsv')
            ->select('MAX(rsv.numeroSoumission)')
            ->where('rsv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $riSoumis = $this->createQueryBuilder('rsv')
            ->select('rsv.numeroItv')
            ->where('rsv.numeroSoumission = :numeroVersionMax')
            ->andWhere('rsv.numeroOR = :numOr')
            ->andWhere('rsv.numeroDit = :numDit')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'numDit' => $numDit,
            ])
            ->getQuery()
            ->getArrayResult();

        return array_column($riSoumis, 'numeroItv');
    }

}