<?php

namespace App\Repository;

use App\DTO\CategorieDTO;
use Doctrine\ORM\EntityRepository;


class IdemniteRepository extends EntityRepository
{
    public function findDistinctByCriteria(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder->select('DISTINCT c.id, c.description')
        ->leftJoin('i.categorie', 'c')
        ->where('i.sousTypeDoc = :sousTypeDoc')
        ->andWhere('i.rmq = :rmq')
        ->setParameter('sousTypeDoc', $criteria['sousTypeDoc'])
        ->setParameter('rmq', $criteria['rmq']);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    
}