<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaSoumisAValidationRepository extends EntityRepository
{
    public function getNumeroVersionMax(string $numeroDemandeAppro)
    {
        $numeroVersionMax = $this->createQueryBuilder('dasav')
            ->select('MAX(dasav.numeroVersion)')
            ->where('dasav.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }
}
