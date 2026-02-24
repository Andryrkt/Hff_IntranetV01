<?php

namespace App\Repository\dom;

use App\Entity\dom\DomSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DomRepository extends EntityRepository
{
    public function findPaginatedAndFilteredAsDTO(int $page, int $limit, DomSearch $domSearch, array $agenceServiceAutorises, bool $listAnnuler = false): array
    {
        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];

        // SELECT uniquement les champs utiles → hydratation scalaire, pas d'entités complètes
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder
            ->select(
                'd.id',
                'd.numeroOrdreMission',
                'd.dateDemande',
                'd.motifDeplacement',
                'd.matricule',
                'd.libelleCodeAgenceService',
                'd.dateDebut',
                'd.dateFin',
                'd.client',
                'd.lieuIntervention',
                'd.totalGeneralPayer',
                'd.devis',
                'd.modePayement',
                's.description  AS statutDescription',
                'td.codeSousType AS codeSousType',
            )
            ->leftJoin('d.sousTypeDocument', 'td')
            ->leftJoin('d.idStatutDemande', 's')
            ->andWhere($listAnnuler ? $queryBuilder->expr()->in('s.id', ':excludedStatuses') : $queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);

        // Filtre pour le statut        
        if (!empty($domSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $domSearch->getStatut() . '%');
        }

        // Filtre pour le type de document
        if (!empty($domSearch->getSousTypeDocument())) {
            $queryBuilder->andWhere('td.codeSousType LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $domSearch->getSousTypeDocument() . '%');
        }

        // Filtrer selon le numero DOM
        if (!empty($domSearch->getNumDom())) {
            $queryBuilder->andWhere('d.numeroOrdreMission = :numDom')
                ->setParameter('numDom', $domSearch->getNumDom());
        }

        // Filtre pour le numero matricule
        if (!empty($domSearch->getMatricule())) {
            $queryBuilder->andWhere('d.matricule = :matricule')
                ->setParameter('matricule', $domSearch->getMatricule());
        }

        // Filtre pour la date de demande (début)
        if (!empty($domSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDemandeDebut')
                ->setParameter('dateDemandeDebut', $domSearch->getDateDebut());
        }

        // Filtre pour la date de demande (fin)
        if (!empty($domSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $domSearch->getDateFin());
        }

        // Filtre pour la date de mission (début)
        if (!empty($domSearch->getDateMissionDebut())) {
            $queryBuilder->andWhere('d.dateDebut >= :dateMissionDebut')
                ->setParameter('dateMissionDebut', $domSearch->getDateMissionDebut());
        }

        // Filtre pour la date de mission (fin)
        if (!empty($domSearch->getDateMissionFin())) {
            $queryBuilder->andWhere('d.dateFin <= :dateMissionFin')
                ->setParameter('dateMissionFin', $domSearch->getDateMissionFin());
        }

        // Filtre pour pièce justificatif
        if (!is_null($domSearch->getPieceJustificatif())) {
            $queryBuilder->andWhere('d.pieceJustificatif = :pieceJustificatif')
                ->setParameter('pieceJustificatif', $domSearch->getPieceJustificatif());
        }

        $queryBuilder
            ->andWhere('d.agenceServiceEmetteur IN (:agenceServiceAutorises)')
            ->setParameter('agenceServiceAutorises', $agenceServiceAutorises)
            ->orderBy('d.numeroOrdreMission', 'DESC');

        // --- COUNT séparé et optimisé (sans ORDER BY) ---
        $countQb = clone $queryBuilder;
        $totalItems = (int) $countQb
            ->select('COUNT(d.id)')
            ->resetDQLPart('orderBy')   // inutile pour le count
            ->getQuery()
            ->getSingleScalarResult();

        // --- Requête paginée ---
        $rows = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult(); // tableau de scalaires, pas d'entités Doctrine

        return [
            'rawRows'     => $rows,
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => (int) ceil($totalItems / $limit),
        ];
    }

    public function findAndFilteredExcel($domSearch, array $agenceServiceAutorises)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.sousTypeDocument', 'td')
            ->leftJoin('d.idStatutDemande', 's');

        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);

        // Filtre pour le statut        
        if (!empty($domSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $domSearch->getStatut() . '%');
        }

        // Filtre pour le type de document
        if (!empty($domSearch->getSousTypeDocument())) {
            $queryBuilder->andWhere('td.codeSousType LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $domSearch->getSousTypeDocument() . '%');
        }

        // Filtrer selon le numero DOM
        if (!empty($domSearch->getNumDom())) {
            $queryBuilder->andWhere('d.numeroOrdreMission = :numDom')
                ->setParameter('numDom', $domSearch->getNumDom());
        }

        // Filtre pour le numero matricule
        if (!empty($domSearch->getMatricule())) {
            $queryBuilder->andWhere('d.matricule = :matricule')
                ->setParameter('matricule', $domSearch->getMatricule());
        }

        // Filtre pour la date de demande (début)
        if (!empty($domSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDemandeDebut')
                ->setParameter('dateDemandeDebut', $domSearch->getDateDebut());
        }

        // Filtre pour la date de demande (fin)
        if (!empty($domSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $domSearch->getDateFin());
        }

        // Filtre pour la date de mission (début)
        if (!empty($domSearch->getDateMissionDebut())) {
            $queryBuilder->andWhere('d.dateDebut >= :dateMissionDebut')
                ->setParameter('dateMissionDebut', $domSearch->getDateMissionDebut());
        }

        // Filtre pour la date de mission (fin)
        if (!empty($domSearch->getDateMissionFin())) {
            $queryBuilder->andWhere('d.dateFin <= :dateMissionFin')
                ->setParameter('dateMissionFin', $domSearch->getDateMissionFin());
        }

        $queryBuilder
            ->andWhere('d.agenceServiceEmetteur IN (:agenceServiceAutorises)')
            ->setParameter('agenceServiceAutorises', $agenceServiceAutorises)
            ->orderBy('d.numeroOrdreMission', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findLastNumtel($matricule)
    {
        try {
            $numTel = $this->createQueryBuilder('d')
                ->select('d.numeroTel')
                ->where('d.matricule = :matricule')
                ->setParameter('matricule', $matricule)
                ->orderBy('d.dateDemande', 'DESC') // Tri décroissant par date ou un autre critère pertinent
                ->setMaxResults(1) // Récupérer seulement le dernier numéro
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            // Si aucun résultat n'est trouvé, retourner null ou une valeur par défaut
            return null;
        }

        return $numTel;
    }
}
