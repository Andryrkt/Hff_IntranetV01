<?php

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class BadmRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ->addSelect('tm', 's');

        if (!empty($criteria['statut'])) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $criteria['statut'] . '%');
        }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFiltered(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

        if (!empty($criteria['statut'])) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $criteria['statut'] . '%');
        }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
