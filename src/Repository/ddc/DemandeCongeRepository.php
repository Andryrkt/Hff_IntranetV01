<?php

namespace App\Repository\ddc;

use App\Entity\admin\Personnel;
use Doctrine\ORM\QueryBuilder;
use App\Entity\ddc\DemandeConge;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DemandeCongeRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(
        int $page,
        int $limit,
        DemandeConge $conge,
        array $options,
        ?User $user = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.agenceServiceirium', 'asi')
            ->addSelect('asi')
            ->leftJoin(Personnel::class, 'p', 'WITH', 'd.matricule = p.Matricule');

        $this->filtredParDate($queryBuilder, $conge, $options);
        $this->filtredParAgenceService($queryBuilder, $options, $user);
        $this->filtredParInformationPrincipal($queryBuilder, $conge, $options);


        // ---------------------------------
        // $query = $queryBuilder->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }

        //-------------------------------------
        $query = $queryBuilder
            ->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.dateDebut', 'DESC')
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

    public function findAndFilteredExcel(DemandeConge $conge, array $options, User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.agenceServiceirium', 'asi')
            ->addSelect('asi')
            ->leftJoin(Personnel::class, 'p', 'WITH', 'd.matricule = p.Matricule');

        $this->filtredParDate($queryBuilder, $conge, $options);
        $this->filtredParAgenceService($queryBuilder, $options, $user);
        $this->filtredParInformationPrincipal($queryBuilder, $conge, $options);


        return $queryBuilder
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    private function filtredParInformationPrincipal(QueryBuilder $queryBuilder, DemandeConge $conge, array $options = []): void
    {
        // Filtrer par Matricule - d'abord regarder si des matricules multiples sont fournis dans les options
        $matriculesToFilter = [];

        // Vérifier si des matricules multiples sont fournis dans les options
        if (isset($options['matricules']) && is_array($options['matricules']) && !empty($options['matricules'])) {
            $matriculesToFilter = $options['matricules'];
        }
        // Sinon, utiliser le matricule de l'entité si c'est une chaîne avec plusieurs valeurs
        elseif ($conge->getMatricule()) {
            $originalMatricule = $conge->getMatricule();
            if (strpos($originalMatricule, ',') !== false) {
                $matricules = explode(',', $originalMatricule);
                $matricules = array_map('trim', $matricules);
                $matricules = array_filter($matricules, function ($value) {
                    return $value !== '';
                });
                $matriculesToFilter = array_values($matricules); // Réindexer le tableau
            } else {
                // Cas unique, on l'ajoute directement
                $matriculesToFilter = [$originalMatricule];
            }
        }

        // Appliquer le filtre si on a des matricules à filtrer
        if (!empty($matriculesToFilter)) {
            $queryBuilder->andWhere('d.matricule IN (:matricules)')
                ->setParameter('matricules', $matriculesToFilter);
        }

        // Filtrer par NumeroDemande
        if ($conge->getNumeroDemande()) {
            $queryBuilder->andWhere('d.numeroDemande = :numeroDemande')
                ->setParameter('numeroDemande', $conge->getNumeroDemande());
        }


        // Filtrer par statut
        if ($conge->getStatutDemande()) {
            $queryBuilder->andWhere('d.statutDemande = :statutDemande')
                ->setParameter('statutDemande', $conge->getStatutDemande());
        }

        // Filtrer par groupe de direction
        if ($conge->getGroupeDirection()) {
            $queryBuilder->andWhere('p.groupeDirection = :groupeDirection')
                ->setParameter('groupeDirection', $conge->getGroupeDirection());
        }
    }

    private function filtredParAgenceService(QueryBuilder $queryBuilder, array $options, User $user): void
    {
        // Filtrer par agence et service autoriser si l'utilisateur n'a pas le role admin
        if (!$options['admin']) {
            $queryBuilder->andWhere('asi.agence_ips IN (:agencesAutorisees)')
                ->andWhere('asi.service_ips IN (:servicesAutorises)')
                ->setParameters([
                    'agencesAutorisees' => $user->getAgenceAutoriserCode(),
                    'servicesAutorises' => $user->getServiceAutoriserCode()
                ]);
        }

        // Filtrer par Agence_Service (code service_sage_paie)
        if (isset($options['agence']) && $options['agence']) {
            $queryBuilder->andWhere('asi.agence_ips = :agenceCode')
                ->setParameter('agenceCode', $options['agence']);
        }

        // Filtrer par service seulement si pas de filtre agenceService
        if (isset($options['service']) && $options['service']) {
            $queryBuilder->andWhere('asi.service_ips = :serviceCode')
                ->setParameter('serviceCode', $options['service']);
        }
    }

    private function filtredParDate(QueryBuilder $queryBuilder, DemandeConge $conge, array $options = []): void
    {
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
    }

    /**
     * recupérer tous les statuts 
     * 
     * cette methode recupère tous les statuts DISTINCT dans le table demande_de_congé
     * et le mettre en ordre ascendante
     * 
     * @return array
     */
    public function getStatut(): array
    {
        return $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutDemande')
            ->orderBy('d.statutDemande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * recupérer tous les matricules, noms, et prénoms
     *
     * cette methode recupère tous les matricules, noms et prénoms DISTINCT dans la table demande_decongé
     * et le mettre en ordre ascendante par rapprt au numéro matricule
     *
     * @param string $query Paramètre de recherche optionnel
     * @return array
     */
    public function getMatriculeNomPrenom(string $query = ''): array
    {
        $qb = $this->createQueryBuilder('d')
            ->select('DISTINCT d.matricule, d.nomPrenoms')
            ->orderBy('d.matricule', 'ASC');

        if (!empty($query)) {
            $qb->andWhere('d.matricule LIKE :query OR d.nomPrenoms LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get tags associated with a specific matricule
     * NOTE: This is a placeholder implementation. In a real system, you would need to
     * define how tags are stored and associated with employees/matricules.
     * This could be from a separate tags table or employee profile data.
     *
     * @param string $matricule
     * @return array
     */
    public function getTagsByMatricule(string $matricule): array
    {
        // This is a placeholder implementation
        // In a real system, you would query a tags table or employee profile table

        // Example: Query a hypothetical employee tags table
        /*
        $qb = $this->getEntityManager()->createQueryBuilder();
        $result = $qb
            ->select('t.tag')
            ->from('App\Entity\Tags', 't') // Replace with actual entity
            ->where('t.matricule = :matricule')
            ->setParameter('matricule', $matricule)
            ->getQuery()
            ->getResult();

        return array_column($result, 'tag');
        */

        // For now, return an empty array - you would implement the actual logic
        return [];
    }
}
