<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitRiSoumisAValidationRepository extends EntityRepository
{
    public function findRiSoumis($numOr, $numDit)
    {
        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $riSoumis = $this->createQueryBuilder('rsv')
            ->select('rsv.numeroItv')
            ->Where('rsv.numeroOR = :numOr')
            ->andWhere('rsv.numeroDit = :numDit')
            ->setParameters([
            
                'numOr' => $numOr,
                'numDit' => $numDit,
            ])
            ->getQuery()
            ->getArrayResult();

        return array_column($riSoumis, 'numeroItv');
    }

}