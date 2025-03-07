<?php

namespace App\Repository\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocumentSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class HistoriqueOperationDocumentRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch = null)
    {
        $queryBuilder = $this->createQueryBuilder('hod');

        $this->dateFinDebut($queryBuilder, $historiqueOperationDocumentSearch);
        $this->conditionSaisieLibre($queryBuilder, $historiqueOperationDocumentSearch);
        $this->conditionListeDeChoix($queryBuilder, $historiqueOperationDocumentSearch);

        $queryBuilder
            ->orderBy('hod.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        $paginator = new DoctrinePaginator($queryBuilder->getQuery());

        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);

        return [
            'data'        => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    private function dateFinDebut($queryBuilder, HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch)
    {
        //filtre date debut
        if (!empty($historiqueOperationDocumentSearch->getDateOperationDebut())) {
            $queryBuilder
                ->andWhere('hod.dateOperation >= :dateDebut')
                ->setParameter('dateDebut', $historiqueOperationDocumentSearch->getDateOperationDebut()->format('Y-m-d'))
                ->andWhere('hod.heureOperation >= :heureDebut')
                ->setParameter('heureDebut', $historiqueOperationDocumentSearch->getDateOperationDebut()->format('Y-m-d'))
            ;
        }

        //filtre date fin
        if (!empty($historiqueOperationDocumentSearch->getDateOperationFin())) {
            $queryBuilder
                ->andWhere('hod.dateOperation <= :dateFin')
                ->setParameter('dateFin', $historiqueOperationDocumentSearch->getDateOperationFin()->format('H:i:s.u'))
                ->andWhere('hod.heureOperation <= :heureFin')
                ->setParameter('heureFin', $historiqueOperationDocumentSearch->getDateOperationFin()->format('H:i:s.u'))
            ;
        }
    }

    private function conditionSaisieLibre($queryBuilder, HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch)
    {
        //filtre selon le numero du document
        if (!empty($historiqueOperationDocumentSearch->getNumeroDocument())) {
            $queryBuilder->andWhere('hod.numeroDocument LIKE :numDoc')
                ->setParameter('numDoc', '%' . $historiqueOperationDocumentSearch->getNumeroDocument() . '%');
        }

        //filtre selon l'utilisateur
        if (!empty($historiqueOperationDocumentSearch->getUtilisateur())) {
            $queryBuilder->andWhere('hod.utilisateur LIKE :utilisateur')
                ->setParameter('utilisateur', '%' . $historiqueOperationDocumentSearch->getUtilisateur() . '%');
        }

        //filtre selon le statut de l'opération
        if (!empty($historiqueOperationDocumentSearch->getStatutOperation())) {
            $queryBuilder->andWhere('hod.statutOperation LIKE :statut')
                ->setParameter('statut', '%' . $historiqueOperationDocumentSearch->getStatutOperation() . '%');
        }
    }

    private function conditionListeDeChoix($queryBuilder, HistoriqueOperationDocumentSearch $historiqueOperationDocumentSearch)
    {
        //filtre pour le niveau d'urgence
        if (!empty($historiqueOperationDocumentSearch->getTypeDocument())) {
            $queryBuilder->andWhere('hod.idTypeDocument = :idDocument')
                ->setParameter('idDocument', $historiqueOperationDocumentSearch->getTypeDocument()->getId());
        }

        //filtre selon le statut
        if (!empty($historiqueOperationDocumentSearch->getTypeOperation())) {
            $queryBuilder->andWhere('hod.idTypeOperation = :idOperation')
                ->setParameter('idOperation',  $historiqueOperationDocumentSearch->getTypeOperation()->getId());
        }
    }
}
