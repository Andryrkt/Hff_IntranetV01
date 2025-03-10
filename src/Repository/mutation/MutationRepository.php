<?php

namespace App\Repository\mutation;

use App\Entity\mutation\MutationSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class MutationRepository extends EntityRepository
{
    public function findLastNumtel($matricule)
    {
        try {
            $numTel = $this->createQueryBuilder('m')
                ->select('m.numeroTel')
                ->where('m.matricule = :matricule')
                ->setParameter('matricule', $matricule)
                ->orderBy('m.dateDemande', 'DESC') // Tri décroissant par date ou un autre critère pertinent
                ->setMaxResults(1) // Récupérer seulement le dernier numéro
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            // Si aucun résultat n'est trouvé, retourner null ou une valeur par défaut
            return null;
        }

        return $numTel;
    }

    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, MutationSearch $mutationSearch)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.statutDemande', 's');

        $idDebut = 66;
        $idEnd = 75;
        $queryBuilder->andWhere($queryBuilder->expr()->between('s.id', ':idDebut', ':idEnd'))
            ->setParameter('idDebut', $idDebut)
            ->setParameter('idEnd', $idEnd)
        ;

        // Filtre pour le statut        
        if (!empty($mutationSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $mutationSearch->getStatut() . '%');
        }

        // Filtrer selon le numero DOM
        if (!empty($mutationSearch->getNumMut())) {
            $queryBuilder->andWhere('m.numeroMutation = :numMut')
                ->setParameter('numMut', $mutationSearch->getNumMut());
        }

        // Filtre pour le numero matricule
        if (!empty($mutationSearch->getMatricule())) {
            $queryBuilder->andWhere('m.matricule = :matricule')
                ->setParameter('matricule', $mutationSearch->getMatricule());
        }

        // Filtre pour la date de demande (début)
        if (!empty($mutationSearch->getDateDemandeDebut())) {
            $queryBuilder->andWhere('m.dateDemande >= :dateDemandeDebut')
                ->setParameter('dateDemandeDebut', $mutationSearch->getDateDemandeDebut());
        }

        // Filtre pour la date de demande (fin)
        if (!empty($mutationSearch->getDateDemandeFin())) {
            $queryBuilder->andWhere('m.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $mutationSearch->getDateDemandeFin());
        }

        // Filtre pour la date de mission (début)
        if (!empty($mutationSearch->getDateMutationDebut())) {
            $queryBuilder->andWhere('m.dateDebut >= :dateMissionDebut')
                ->setParameter('dateMissionDebut', $mutationSearch->getDateMutationDebut());
        }

        // Filtre pour la date de mission (fin)
        if (!empty($mutationSearch->getDateMutationFin())) {
            $queryBuilder->andWhere('m.dateFin <= :dateMissionFin')
                ->setParameter('dateMissionFin', $mutationSearch->getDateMutationFin());
        }

        // Ordre et pagination
        $queryBuilder->orderBy('m.numeroMutation', 'DESC')
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
