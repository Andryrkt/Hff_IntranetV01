<?php

namespace App\Repository\ddc;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\ddc\DemandeConge;

class DemandeCongeRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(
        int $page,
        int $limit,
        DemandeConge $conge,
        array $options
    ): array {
        $queryBuilder = $this->createQueryBuilder('d');

        if ($conge->getMatricule()) {
            $queryBuilder->andWhere('d.matricule = :matricule')
                ->setParameter('matricule', $conge->getMatricule());
        }

        if ($conge->getNumeroDemande()) {
            $queryBuilder->andWhere('d.numeroDemande = :numeroDemande')
                ->setParameter('numeroDemande', $conge->getNumeroDemande());
        }

        // Filtrer par plage de date de demande selon les règles spécifiées
        if (isset($options['dateDemande']) && isset($options['dateDemandeFin'])) {
            // Si les deux dates sont renseignées, rechercher entre ces deux dates
            $queryBuilder->andWhere('d.dateDemande BETWEEN :dateDemande AND :dateDemandeFin')
                ->setParameter('dateDemande', $options['dateDemande'])
                ->setParameter('dateDemandeFin', $options['dateDemandeFin']);
        } else if (isset($options['dateDemande'])) {
            // Si seule la date de début est renseignée, rechercher les dates supérieures ou égales
            $queryBuilder->andWhere('d.dateDemande >= :dateDemande')
                ->setParameter('dateDemande', $options['dateDemande']);
        } else if (isset($options['dateDemandeFin'])) {
            // Si seule la date de fin est renseignée, rechercher les dates inférieures ou égales
            $queryBuilder->andWhere('d.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $options['dateDemandeFin']);
        }

        // Filtrer par plage de date de congé
        if ($conge->getDateDebut() && $conge->getDateFin()) {
            $queryBuilder->andWhere('d.dateDebut >= :dateDebut')
                ->andWhere('d.dateFin <= :dateFin')
                ->setParameter('dateDebut', $conge->getDateDebut())
                ->setParameter('dateFin', $conge->getDateFin());
        } else if ($conge->getDateDebut()) {
            $queryBuilder->andWhere('d.dateDebut >= :dateDebut')
                ->setParameter('dateDebut', $conge->getDateDebut());
        } else if ($conge->getDateFin()) {
            $queryBuilder->andWhere('d.dateFin <= :dateFin')
                ->setParameter('dateFin', $conge->getDateFin());
        }

        // Filtrer par Agence_Service (ex. : 'PER' ou 'BR80 - A102')
        if (isset($options['agenceService']) && $options['agenceService']) {
            $queryBuilder->andWhere('d.agenceService = :agenceService')
                ->setParameter('agenceService', $options['agenceService']);
        }
        // Filtrer par Agence_Debiteur
        elseif (isset($options['agence']) && $options['agence']) {
            $queryBuilder->andWhere('d.agenceDebiteur = :agenceDebiteur')
                ->setParameter('agenceDebiteur', $options['agence']);
        }
        // Filtrer par service seulement si pas de filtre agenceService
        elseif (isset($options['service']) && $options['service']) {
            $queryBuilder->andWhere('d.agenceService LIKE :servicePattern')
                ->setParameter('servicePattern', '% - ' . $options['service']);
        }

        // Filtrer par statut
        if ($conge->getStatutDemande()) {
            $queryBuilder->andWhere('d.statutDemande = :statutDemande')
                ->setParameter('statutDemande', $conge->getStatutDemande());
        }

        // Filtrer par agences autorisées si nécessaire
        if (isset($options['idAgence']) && !empty($options['idAgence']) && !isset($options['agenceService'])) {
            if (is_array($options['idAgence'])) {
                $orExpressions = [];
                foreach ($options['idAgence'] as $key => $agenceId) {
                    $paramName = 'agenceId' . $key;
                    $orExpressions[] = $queryBuilder->expr()->like('d.agenceService', ':' . $paramName);
                    $queryBuilder->setParameter($paramName, $agenceId . ' - %');
                }
                if (!empty($orExpressions)) {
                    $queryBuilder->andWhere($queryBuilder->expr()->orX()->addMultiple($orExpressions));
                }
            }
        }

        $query = $queryBuilder
            ->orderBy('d.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $pagesCount = (int) ceil($totalItems / $limit);

        return [
            'data' => $paginator->getIterator(),
            'currentPage' => $page,
            'lastPage' => $pagesCount,
            'totalItems' => $totalItems
        ];
    }

    public function findAndFilteredExcel(DemandeConge $conge, array $options): array
    {
        $queryBuilder = $this->createQueryBuilder('d');

        if ($conge->getMatricule()) {
            $queryBuilder->andWhere('d.matricule = :matricule')
                ->setParameter('matricule', $conge->getMatricule());
        }

        if ($conge->getNumeroDemande()) {
            $queryBuilder->andWhere('d.numeroDemande = :numeroDemande')
                ->setParameter('numeroDemande', $conge->getNumeroDemande());
        }

        // Filtrer par plage de date de demande selon les règles spécifiées
        if (isset($options['dateDemande']) && isset($options['dateDemandeFin'])) {
            // Si les deux dates sont renseignées, rechercher entre ces deux dates
            $queryBuilder->andWhere('d.dateDemande BETWEEN :dateDemande AND :dateDemandeFin')
                ->setParameter('dateDemande', $options['dateDemande'])
                ->setParameter('dateDemandeFin', $options['dateDemandeFin']);
        } else if (isset($options['dateDemande'])) {
            // Si seule la date de début est renseignée, rechercher les dates supérieures ou égales
            $queryBuilder->andWhere('d.dateDemande >= :dateDemande')
                ->setParameter('dateDemande', $options['dateDemande']);
        } else if (isset($options['dateDemandeFin'])) {
            // Si seule la date de fin est renseignée, rechercher les dates inférieures ou égales
            $queryBuilder->andWhere('d.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $options['dateDemandeFin']);
        }

        // Filtrer par plage de date de congé
        if ($conge->getDateDebut() && $conge->getDateFin()) {
            $queryBuilder->andWhere('d.dateDebut >= :dateDebut')
                ->andWhere('d.dateFin <= :dateFin')
                ->setParameter('dateDebut', $conge->getDateDebut())
                ->setParameter('dateFin', $conge->getDateFin());
        } else if ($conge->getDateDebut()) {
            $queryBuilder->andWhere('d.dateDebut >= :dateDebut')
                ->setParameter('dateDebut', $conge->getDateDebut());
        } else if ($conge->getDateFin()) {
            $queryBuilder->andWhere('d.dateFin <= :dateFin')
                ->setParameter('dateFin', $conge->getDateFin());
        }

        // Filtrer par Agence_Service (ex. : 'PER' ou 'BR80 - A102')
        if (isset($options['agenceService']) && $options['agenceService']) {
            $queryBuilder->andWhere('d.agenceService = :agenceService')
                ->setParameter('agenceService', $options['agenceService']);
        }
        // Filtrer par Agence_Debiteur
        elseif (isset($options['agence']) && $options['agence']) {
            $queryBuilder->andWhere('d.agenceDebiteur = :agenceDebiteur')
                ->setParameter('agenceDebiteur', $options['agence']);
        }
        // Filtrer par service seulement si pas de filtre agenceService
        elseif (isset($options['service']) && $options['service']) {
            $queryBuilder->andWhere('d.agenceService LIKE :servicePattern')
                ->setParameter('servicePattern', '% - ' . $options['service']);
        }

        // Filtrer par statut
        if ($conge->getStatutDemande()) {
            $queryBuilder->andWhere('d.statutDemande = :statutDemande')
                ->setParameter('statutDemande', $conge->getStatutDemande());
        }

        // Filtrer par agences autorisées si nécessaire
        if (isset($options['idAgence']) && !empty($options['idAgence']) && !isset($options['agenceService'])) {
            if (is_array($options['idAgence'])) {
                $orExpressions = [];
                foreach ($options['idAgence'] as $key => $agenceId) {
                    $paramName = 'agenceId' . $key;
                    $orExpressions[] = $queryBuilder->expr()->like('d.agenceService', ':' . $paramName);
                    $queryBuilder->setParameter($paramName, $agenceId . ' - %');
                }
                if (!empty($orExpressions)) {
                    $queryBuilder->andWhere($queryBuilder->expr()->orX()->addMultiple($orExpressions));
                }
            }
        }

        return $queryBuilder
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
