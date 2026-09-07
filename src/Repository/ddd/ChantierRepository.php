<?php

namespace App\Repository\ddd;

use Doctrine\ORM\EntityRepository;

class ChantierRepository extends EntityRepository
{
    public function getChantiers(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.nomChantier', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
