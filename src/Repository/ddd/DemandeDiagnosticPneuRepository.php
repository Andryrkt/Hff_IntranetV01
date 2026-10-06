<?php

namespace App\Repository\ddd;

use App\Entity\ddd\DemandeDiagnosticPneu;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DemandeDiagnosticPneuRepository extends EntityRepository
{




    /**
     * Liste des demandes diagnostic pneu filtrées
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     *
     * @return array
     */
    public function findPaginatedAndFiltered(
        int $page = 1,
        int $limit = 10,
        array $filters = []
    ): array {

        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.chantier', 'c')
            ->addSelect('c');


        /**
         * Filtre statut
         */
        if (!empty($filters['statut'])) {
            $qb
                ->andWhere('d.statut = :statut')
                ->setParameter('statut', $filters['statut']);
        }


        /**
         * Recherche numéro demande
         */
        if (!empty($filters['numeroDemande'])) {

            $qb
                ->andWhere('d.numeroDemande LIKE :numeroDemande')
                ->setParameter(
                    'numeroDemande',
                    '%' . $filters['numeroDemande'] . '%'
                );
        }


        /**
         * Filtre chantier
         */
        if (!empty($filters['chantierId'])) {

            $qb
                ->andWhere('c.idChantier = :chantierId')
                ->setParameter(
                    'chantierId',
                    $filters['chantierId']
                );
        }


        /**
         * Filtre matériel
         */
        if (!empty($filters['idMateriel'])) {

            $qb
                ->andWhere('d.idMateriel = :idMateriel')
                ->setParameter(
                    'idMateriel',
                    $filters['idMateriel']
                );
        }


        /**
         * Filtre numéro parc
         */
        if (!empty($filters['numeroParcMateriel'])) {

            $qb
                ->andWhere('d.numeroParcMateriel LIKE :numeroParc')
                ->setParameter(
                    'numeroParc',
                    '%' . $filters['numeroParcMateriel'] . '%'
                );
        }


        /**
         * Filtre période
         */
        if (!empty($filters['dateDebut'])) {

            $qb
                ->andWhere('d.dateCreation >= :dateDebut')
                ->setParameter(
                    'dateDebut',
                    $filters['dateDebut']
                );
        }


        if (!empty($filters['dateFin'])) {

            $qb
                ->andWhere('d.dateCreation <= :dateFin')
                ->setParameter(
                    'dateFin',
                    $filters['dateFin']
                );
        }


        /**
         * Tri
         */
        $qb
            ->orderBy('d.dateCreation', 'DESC')
            ->addOrderBy('d.numeroDemande', 'ASC');


        /**
         * Pagination
         */
        $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);


        $paginator = new DoctrinePaginator(
            $qb->getQuery()
        );


        $totalItems = count($paginator);

        $lastPage = (int) ceil(
            $totalItems / $limit
        );


        /**
         * Nombre par statut
         */
        $statusCounts = $this->countByStatus();


        return [
            'data' => iterator_to_array(
                $paginator->getIterator()
            ),
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'statusCounts' => $statusCounts,
        ];
    }
}
