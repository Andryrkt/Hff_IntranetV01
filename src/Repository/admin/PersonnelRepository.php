<?php

namespace App\Repository\admin;

use Doctrine\ORM\EntityRepository;

class PersonnelRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b');

        if (!empty($criteria['matricule'])) {
            $queryBuilder->andWhere('b.Matricule LIKE :matricule')
                ->setParameter('matricule', '%'. $criteria['matricule'] . '%');
        }

        $queryBuilder->orderBy('b.Matricule', 'DESC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFiltered(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)');

        if (!empty($criteria['matricule'])) {
            $queryBuilder->andWhere('b.Matricule LIKE :matricule')
                ->setParameter('matricule', '%' . $criteria['matricule'] .'%');
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}