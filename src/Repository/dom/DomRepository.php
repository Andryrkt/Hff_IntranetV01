<?php

namespace App\Repository\dom;

use App\Entity\dom\DomSearch;
use App\Entity\dom\Dom;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\QueryBuilder;

class DomRepository extends EntityRepository
{
    /**
     * Trouve les DOM avec pagination et filtres optimisés
     */
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, DomSearch $domSearch, array $options): array
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $this->applyFilters($queryBuilder, $domSearch, $options);
        $this->applyPagination($queryBuilder, $page, $limit);

        // Pagination optimisée
        $paginator = new DoctrinePaginator($queryBuilder);
        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);

        return [
            'data' => iterator_to_array($paginator->getIterator()),
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'hasNextPage' => $page < $lastPage,
            'hasPreviousPage' => $page > 1
        ];
    }

    /**
     * Crée le QueryBuilder de base avec les jointures optimisées
     */
    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.sousTypeDocument', 'td')
            ->addSelect('td')
            ->leftJoin('d.idStatutDemande', 's')
            ->addSelect('s')
            ->leftJoin('d.agenceEmetteur', 'ae')
            ->addSelect('ae')
            ->leftJoin('d.serviceEmetteur', 'se')
            ->addSelect('se')
            ->leftJoin('d.categorie', 'c')
            ->addSelect('c')
            ->leftJoin('d.site', 'site')
            ->addSelect('site');
    }

    /**
     * Applique les filtres de recherche
     */
    private function applyFilters(QueryBuilder $queryBuilder, DomSearch $domSearch, array $options): void
    {
        // Statuts exclus
        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);

        // Filtres dynamiques
        $this->applySearchFilters($queryBuilder, $domSearch);
        $this->applyAuthorizationFilters($queryBuilder, $options);
    }

    /**
     * Applique les filtres de recherche
     */
    private function applySearchFilters(QueryBuilder $queryBuilder, DomSearch $domSearch): void
    {
        // Filtre par statut
        if (!empty($domSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $domSearch->getStatut() . '%');
        }

        // Filtre par type de document
        if (!empty($domSearch->getSousTypeDocument())) {
            $queryBuilder->andWhere('td.codeSousType LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $domSearch->getSousTypeDocument() . '%');
        }

        // Filtre par numéro DOM
        if (!empty($domSearch->getNumDom())) {
            $queryBuilder->andWhere('d.numeroOrdreMission = :numDom')
                ->setParameter('numDom', $domSearch->getNumDom());
        }

        // Filtre par matricule
        if (!empty($domSearch->getMatricule())) {
            $queryBuilder->andWhere('d.matricule = :matricule')
                ->setParameter('matricule', $domSearch->getMatricule());
        }

        // Filtres de dates
        $this->applyDateFilters($queryBuilder, $domSearch);

        // Filtre par pièce justificative
        if (!is_null($domSearch->getPieceJustificatif())) {
            $queryBuilder->andWhere('d.pieceJustificatif = :pieceJustificatif')
                ->setParameter('pieceJustificatif', $domSearch->getPieceJustificatif());
        }
    }

    /**
     * Applique les filtres de dates
     */
    private function applyDateFilters(QueryBuilder $queryBuilder, DomSearch $domSearch): void
    {
        // Date de demande
        if (!empty($domSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDemandeDebut')
                ->setParameter('dateDemandeDebut', $domSearch->getDateDebut());
        }

        if (!empty($domSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $domSearch->getDateFin());
        }

        // Date de mission
        if (!empty($domSearch->getDateMissionDebut())) {
            $queryBuilder->andWhere('d.dateDebut >= :dateMissionDebut')
                ->setParameter('dateMissionDebut', $domSearch->getDateMissionDebut());
        }

        if (!empty($domSearch->getDateMissionFin())) {
            $queryBuilder->andWhere('d.dateFin <= :dateMissionFin')
                ->setParameter('dateMissionFin', $domSearch->getDateMissionFin());
        }
    }

    /**
     * Applique les filtres d'autorisation
     */
    private function applyAuthorizationFilters(QueryBuilder $queryBuilder, array $options): void
    {
        if (!$options['boolean']) {
            $agenceIdAutoriser = is_array($options['idAgence']) ? $options['idAgence'] : [$options['idAgence']];
            $queryBuilder->andWhere('d.agenceEmetteurId IN (:agenceIdAutoriser)')
                ->setParameter('agenceIdAutoriser', $agenceIdAutoriser);
        }
    }

    /**
     * Applique la pagination
     */
    private function applyPagination(QueryBuilder $queryBuilder, int $page, int $limit): void
    {
        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    }

    /**
     * Trouve les DOM pour export Excel
     */
    public function findAndFilteredExcel(DomSearch $domSearch, array $options): array
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $this->applyFilters($queryBuilder, $domSearch, $options);
        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Trouve les DOM annulés avec pagination
     */
    public function findPaginatedAndFilteredAnnuler(int $page = 1, int $limit = 10, DomSearch $domSearch, array $options): array
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        
        // Statuts annulés uniquement
        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
        $queryBuilder->andWhere($queryBuilder->expr()->in('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);

        $this->applySearchFilters($queryBuilder, $domSearch);
        $this->applyAuthorizationFilters($queryBuilder, $options);
        $this->applyPagination($queryBuilder, $page, $limit);

        $paginator = new DoctrinePaginator($queryBuilder);
        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);

        return [
            'data' => iterator_to_array($paginator->getIterator()),
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'hasNextPage' => $page < $lastPage,
            'hasPreviousPage' => $page > 1
        ];
    }

    /**
     * Trouve le dernier numéro de téléphone pour un matricule
     */
    public function findLastNumtel(string $matricule): ?string
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('d.numeroTel')
                ->where('d.matricule = :matricule')
                ->andWhere('d.numeroTel IS NOT NULL')
                ->setParameter('matricule', $matricule)
                ->orderBy('d.dateDemande', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /**
     * Trouve les DOM par critères avec cache
     */
    public function findByCriteriaWithCache(array $criteria, int $limit = 50): array
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        
        foreach ($criteria as $field => $value) {
            if ($value !== null) {
                $queryBuilder->andWhere("d.{$field} = :{$field}")
                    ->setParameter($field, $value);
            }
        }

        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600) // Cache pendant 1 heure
            ->getResult();
    }

    /**
     * Compte les DOM par statut
     */
    public function countByStatus(): array
    {
        return $this->createQueryBuilder('d')
            ->select('s.description as statut, COUNT(d.id) as count')
            ->leftJoin('d.idStatutDemande', 's')
            ->groupBy('s.id, s.description')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les DOM expirés
     */
    public function findExpiredDom(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.dateFin < :now')
            ->andWhere('d.idStatutDemande NOT IN (:excludedStatuses)')
            ->setParameter('now', new \DateTime())
            ->setParameter('excludedStatuses', [9, 33, 34, 35, 44])
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les DOM avec chevauchement de dates
     */
    public function findOverlappingDom(string $matricule, \DateTime $dateDebut, \DateTime $dateFin, ?int $excludeId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.matricule = :matricule')
            ->andWhere('d.idStatutDemande NOT IN (:excludedStatuses)')
            ->andWhere('(d.dateDebut <= :dateFin AND d.dateFin >= :dateDebut)')
            ->setParameter('matricule', $matricule)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('excludedStatuses', [9, 33, 34, 35, 44]);

        if ($excludeId) {
            $queryBuilder->andWhere('d.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}