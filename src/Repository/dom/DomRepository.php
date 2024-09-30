<?php

namespace App\Repository\dom;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DomRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [], array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')

            ;


        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        $paginator = new DoctrinePaginator($queryBuilder->getQuery());

        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);
        
    return [
        'data' => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
        'totalItems' => $totalItems,
        'currentPage' => $page,
        'lastPage' => $lastPage,
    ];
    }
}