<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaHistoriqueDemandeModifDARepository extends EntityRepository
{
    public function findNumDaOfNonDeverrouillees(): array
    {
        $results = $this->createQueryBuilder('h')
            ->select('h.numDa')
            ->andWhere('h.estDeverouillee = :val')
            ->setParameter('val', false)
            ->getQuery()
            ->getResult();

        return array_column($results, 'numDa');
    }
}
