<?php

namespace App\Repository\cas;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class CasierRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if (! empty($criteria['casier'])) {
            $queryBuilder->andWhere('c.casier LIKE :casier')
                ->setParameter('casier',  $criteria['casier']);
        }

        //filtre selon l'agence debiteur
        if (! empty($criteria['agence'])) {
            $queryBuilder->andWhere('c.agenceRattacher = :agRatch')
                ->setParameter('agRatch',  $criteria['agence']->getId());
        }

        $queryBuilder->orderBy('c.numeroCas', 'DESC');
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

    public function findPaginatedAndFilteredTemporaire(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if (! empty($criteria['casier'])) {
            $queryBuilder->andWhere('c.casier LIKE :casier')
                ->setParameter('casier',  $criteria['casier']);
        }

        //filtre selon l'agence debiteur
        if (! empty($criteria['agence'])) {
            $queryBuilder->andWhere('c.agenceRattacher = :agRatch')
                ->setParameter('agRatch',  $criteria['agence']->getId());
        }

        $queryBuilder
            ->andWhere('c.idStatutDemande = :idStatut')
            ->setParameter('idStatut', 55)
            ->orderBy('c.numeroCas', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
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
