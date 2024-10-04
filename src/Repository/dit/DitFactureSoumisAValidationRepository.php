<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitFactureSoumisAValidationRepository extends EntityRepository
{
    public function findNbrFact($numfact)
    {
        $nbrfact = $this->createQueryBuilder('fsv')
            ->select('COUNT(fsv.numeroFact)')
            ->where('fsv.numeroFact = :numfact')  // Suppression des parenthèses inutiles
            ->setParameter('numfact', $numfact)
            ->getQuery()
            ->getSingleScalarResult();

        return $nbrfact ? $nbrfact : 0;
    }
}