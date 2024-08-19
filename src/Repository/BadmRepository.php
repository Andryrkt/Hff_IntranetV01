<?php

namespace App\Repository;



use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class BadmRepository extends EntityRepository
{

    public function findIdMateriel()
    {
        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];

        $queryBuilder = $this->createQueryBuilder('d')
            ->select('DISTINCT d.idMateriel')
            ->leftJoin('d.statutDemande', 's');
            $queryBuilder->where($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);

            $results = $queryBuilder->getQuery()->getArrayResult();

            // Extraire les IDs des matériels dans un tableau simple
            $idMateriels = array_column($results, 'idMateriel');
            
            return $idMateriels;
    }

    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }

        if (!empty($criteria['idMateriel'])) {
            $queryBuilder->andWhere('b.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $criteria['idMateriel'] );
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        $queryBuilder->orderBy('b.numBadm', 'DESC');
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

    
    public function findAndFilteredExcel( array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }

        if (!empty($criteria['idMateriel'])) {
            $queryBuilder->andWhere('b.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $criteria['idMateriel'] );
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        $queryBuilder->orderBy('b.numBadm', 'DESC');
            

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPaginatedAndFilteredListAnnuler(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }

        if (!empty($criteria['idMateriel'])) {
            $queryBuilder->andWhere('b.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $criteria['idMateriel'] );
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        $queryBuilder->orderBy('b.numBadm', 'DESC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        
            // $sql = $queryBuilder->getQuery()->getSQL();
            // echo $sql;

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFilteredListAnnuller(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

        if (!empty($criteria['typeMouvement'])) {
            $queryBuilder->andWhere('tm.description LIKE :typeMouvement')
                ->setParameter('typeMouvement', '%' . $criteria['typeMouvement'] . '%');
        }
        
        if (!empty($criteria['idMateriel'])) {
            $queryBuilder->andWhere('b.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $criteria['idMateriel'] );
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