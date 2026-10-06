<?php

namespace App\Repository\admin;


use Doctrine\ORM\EntityRepository;


class AgenceRepository extends EntityRepository
{
    public function findIdByCodeAgence(string $codeAgence): ?int
    {
        return $this->createQueryBuilder('a')
            ->select('a.id')
            ->where('a.codeAgence = :codeAgence')
            ->setParameter('codeAgence', $codeAgence)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
