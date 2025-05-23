<?php

namespace App\Repository\dw;

use App\Entity\dw\DocInternesearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DwProcessusProcedureRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, DocInternesearch $docInternesearch)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        if (!empty($docInternesearch->getDateDocument())) {
            $queryBuilder->andWhere('d.dateDocument = :dateDoc')
                ->setParameter('dateDoc', $docInternesearch->getDateDocument());
        }

        if (!empty($docInternesearch->getNomDocument())) {
            $queryBuilder->andWhere('d.nomDocument LIKE :nomDoc')
                ->setParameter('nomDoc', '%' . $docInternesearch->getNomDocument() . '%');
        }

        if (!empty($docInternesearch->getTypeDocument())) {
            $queryBuilder->andWhere('d.typeDocument LIKE :typeDoc')
                ->setParameter('typeDoc', '%' . $docInternesearch->getTypeDocument() . '%');
        }

        if (!empty($docInternesearch->getPerimetre())) {
            $queryBuilder->andWhere('d.perimetre LIKE :perimetre')
                ->setParameter('perimetre', '%' . $docInternesearch->getPerimetre() . '%');
        }

        if (!empty($docInternesearch->getProcessusLie())) {
            $queryBuilder->andWhere('d.processusLie LIKE :proc')
                ->setParameter('proc', '%' . $docInternesearch->getProcessusLie() . '%');
        }

        if (!empty($docInternesearch->getNomResponsable())) {
            $queryBuilder->andWhere('d.nomResponsable LIKE :responsable')
                ->setParameter('responsable', '%' . $docInternesearch->getNomResponsable() . '%');
        }

        // Ordre et pagination
        $queryBuilder->orderBy('d.idDocument', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // Pagination
        $paginator = new DoctrinePaginator($queryBuilder);
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