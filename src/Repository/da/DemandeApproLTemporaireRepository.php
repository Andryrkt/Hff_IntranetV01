<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DemandeApproLTemporaireRepository extends EntityRepository
{
    public function getNumeroVersionMax(string $numeroDemandeAppro)
    {
        $numeroVersionMax = $this->createQueryBuilder('dal')
            ->select('MAX(dal.numeroVersion)')
            ->where('dal.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }
}
