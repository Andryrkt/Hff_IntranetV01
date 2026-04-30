<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwCommandeRepository extends EntityRepository
{
    public function findNumCdeDw(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.numeroCde as numcde')
            ->where('c.path IS NOT NULL')
            ->orderBy('c.numeroCde', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
