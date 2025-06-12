<?php

namespace App\Repository\dom;

use App\Entity\dom\DomSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DomRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, DomSearch $domSearch, array $options)
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


        if (!$options['boolean']) {
            //ceci est figer pour les utilisateur autre que l'administrateur
            $agenceIdAutoriser = is_array($options['idAgence']) ? $options['idAgence'] : [$options['idAgence']];
            $queryBuilder->andWhere('d.agenceEmetteurId IN (:agenceIdAutoriser)')
                ->setParameter('agenceIdAutoriser', $agenceIdAutoriser);
        }

        // Ordre et pagination
        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC')
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

    public function findAndFilteredExcel($domSearch, array $options)
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


        if (!$options['boolean']) {
            //ceci est figer pour les utilisateur autre que l'administrateur
            $agenceIdAutoriser = is_array($options['idAgence']) ? $options['idAgence'] : [$options['idAgence']];
            $queryBuilder->andWhere('d.agenceEmetteurId IN (:agenceIdAutoriser)')
                ->setParameter('agenceIdAutoriser', $agenceIdAutoriser);
        }

        // Ordre et pagination
        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC');


        return $queryBuilder->getQuery()->getResult();
    }

    public function findPaginatedAndFilteredAnnuler(int $page = 1, int $limit = 10, DomSearch $domSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.sousTypeDocument', 'td')
            ->leftJoin('d.idStatutDemande', 's');

        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
        $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':excludedStatuses'))
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


        if (!$options['boolean']) {
            //ceci est figer pour les utilisateur autre que l'administrateur
            $agenceIdAutoriser = is_array($options['idAgence']) ? $options['idAgence'] : [$options['idAgence']];
            $queryBuilder->andWhere('d.agenceEmetteurId IN (:agenceIdAutoriser)')
                ->setParameter('agenceIdAutoriser', $agenceIdAutoriser);
        }



        // Ordre et pagination
        $queryBuilder->orderBy('d.numeroOrdreMission', 'DESC')
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
