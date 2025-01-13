<?php

namespace App\Repository\admin\dom;


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

    public function findIdemnite(array $criteria)
    {
        return  $this->createQueryBuilder('i')
    ->where('i.sousTypeDoc = :sousTypeDoc')
    ->andWhere('i.rmq = :rmq')
    ->andWhere('i.categorie = :categorie')
    ->setParameter('sousTypeDoc', $criteria['sousTypeDoc'])
    ->setParameter('rmq', $criteria['rmq'])
    ->setParameter('categorie', $criteria['categorie'])
    ->getQuery()
    ->getResult();
    }
}