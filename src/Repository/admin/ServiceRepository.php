<?php

namespace App\Repository\admin;


use App\Entity\admin\Agence;
use Doctrine\ORM\EntityRepository;


class ServiceRepository extends EntityRepository
{

    public function findByAgence(Agence $agence)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.agence = :agence')
            ->setParameter('agence', $agence)
            ->orderBy('s.codeService', 'ASC')
            ->getQuery()
            ->getResult();
    }
}