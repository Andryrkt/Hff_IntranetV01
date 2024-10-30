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

    public function findNumItv($numOr)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('rsv')
            ->select('MAX(rsv.numeroSoumission)')
            ->where('rsv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();


            // Étape 2 : Utiliser le numeroVersionMax pour récupérer le numero d'intervention
            $nbrItv = $this->createQueryBuilder('rsv')
                ->select('rsv.numeroItv')  
                ->where('rsv.numeroOR = :numOr') 
                ->andwhere('rsv.numeroSoumission = :numeroVersionMax')
                ->setParameters([
                    'numeroVersionMax' => $numeroVersionMax,
                    'numOr' => $numOr,
                ])
                ->getQuery()
                ->getSingleColumnResult();
    
            return $nbrItv;
    }

    public function findNbreNumItv($numOr)
    {
            // Étape 2 : Utiliser le numeroVersionMax pour récupérer le numero d'intervention
            $nbrItv = $this->createQueryBuilder('rsv')
                ->select('COUNT(rsv.numeroItv)')  
                ->where('rsv.numeroOR = :numOr') 
                ->setParameters([
                    'numOr' => $numOr,
                ])
                ->getQuery()
                ->getSingleColumnResult();
    
            return $nbrItv;
    }

}