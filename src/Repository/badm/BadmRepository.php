<?php

namespace App\Repository\badm;



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

    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [], int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

        $this->filtredExcludeStatut($queryBuilder);

        $this->filtredCondition($queryBuilder, $criteria);

        $this->filtredAgenceServiceEmetteur($queryBuilder, $criteria);

        $this->filtredAgenceServiceDebiteur($queryBuilder, $criteria);

        // Condition sur les couples agences-services
        $this->conditionAgenceService($queryBuilder, $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);

        $queryBuilder
            ->orderBy('b.numBadm', 'DESC')
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


    public function findAndFilteredExcel(array $criteria = [], int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

        $this->filtredExcludeStatut($queryBuilder);

        $this->filtredCondition($queryBuilder, $criteria);

        $this->filtredAgenceServiceEmetteur($queryBuilder, $criteria);

        $this->filtredAgenceServiceDebiteur($queryBuilder, $criteria);

        // Condition sur les couples agences-services
        $this->conditionAgenceService($queryBuilder, $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);

        $queryBuilder
            ->orderBy('b.numBadm', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }


    public function findPaginatedAndFilteredListAnnuler(int $page = 1, int $limit = 10, array $criteria = [], int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

        $this->filtredExcludeStatut($queryBuilder);

        $this->filtredCondition($queryBuilder, $criteria);


        $this->filtredAgenceServiceEmetteur($queryBuilder, $criteria);

        $this->filtredAgenceServiceDebiteur($queryBuilder, $criteria);

        // Condition sur les couples agences-services
        $this->conditionAgenceService($queryBuilder, $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;


        // $sql = $queryBuilder->getQuery()->getSQL();
        // echo $sql;

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


    private function filtredAgenceServiceEmetteur($queryBuilder, $criteria)
    {
        //filtre selon l'agence emettteur
        if (!empty($criteria['agenceEmetteur'])) {
            $queryBuilder->andWhere('b.agenceEmetteurId = :agEmet')
                ->setParameter('agEmet',  $criteria['agenceEmetteur']);
        }

        //filtre selon le service emetteur
        if (!empty($criteria['serviceEmetteur'])) {
            $queryBuilder->andWhere('b.serviceEmetteurId = :agServEmet')
                ->setParameter('agServEmet', $criteria['serviceEmetteur']);
        }
    }

    private function filtredExcludeStatut($queryBuilder)
    {
        $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $excludedStatuses);
    }

    private function filtredCondition($queryBuilder, $criteria)
    {
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
                ->setParameter('idMateriel',  $criteria['idMateriel']);
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }
    }

    private function filtredAgenceServiceDebiteur($queryBuilder, $criteria)
    {
        //filtre selon l'agence debiteur
        if (!empty($criteria['agenceDebiteur'])) {
            $queryBuilder->andWhere('b.agenceDebiteurId = :agDebit')
                ->setParameter('agDebit',  $criteria['agenceDebiteur']);
        }

        //filtre selon le service debiteur
        if (!empty($criteria['serviceDebiteur'])) {
            $queryBuilder->andWhere('b.serviceDebiteurId = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $criteria['serviceDebiteur']);
        }
    }

    private function conditionAgenceService($queryBuilder, int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $ORX = $queryBuilder->expr()->orX();

        // 1- Emetteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('b.agenceEmetteurId', ':agEmetteur'),
                $queryBuilder->expr()->eq('b.serviceEmetteurId', ':servEmetteur')
            )
        );
        $queryBuilder->setParameter('agEmetteur', $agenceIdUser);
        $queryBuilder->setParameter('servEmetteur', $serviceIdUser);

        // 2- Debiteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('b.agenceDebiteurId', ':agDebiteur'),
                $queryBuilder->expr()->eq('b.serviceDebiteurId', ':servDebiteur')
            )
        );
        $queryBuilder->setParameter('agDebiteur', $agenceIdUser);
        $queryBuilder->setParameter('servDebiteur', $serviceIdUser);

        // 3- Emetteur et Débiteur : agence et service autorisés du profil
        if (!empty($agenceServiceAutorises)) {
            $orX1 = $queryBuilder->expr()->orX(); // Pour émetteur
            $orX2 = $peutVoirListeAvecDebiteur ? $queryBuilder->expr()->orX() : null; // Pour débiteur : n'autoriser que si le profil peut voir la liste avec le débiteur
            foreach ($agenceServiceAutorises as $i => $tab) {
                $orX1->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('b.agenceEmetteurId', ':agEmetteur_' . $i),
                        $queryBuilder->expr()->eq('b.serviceEmetteurId', ':servEmetteur_' . $i)
                    )
                );
                $queryBuilder->setParameter('agEmetteur_' . $i, $tab['agence_id']);
                $queryBuilder->setParameter('servEmetteur_' . $i, $tab['service_id']);
                if ($orX2) {
                    $orX2->add(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('b.agenceDebiteurId', ':agDebiteur_' . $i),
                            $queryBuilder->expr()->eq('b.serviceDebiteurId', ':servDebiteur_' . $i)
                        )
                    );
                    $queryBuilder->setParameter('agDebiteur_' . $i, $tab['agence_id']);
                    $queryBuilder->setParameter('servDebiteur_' . $i, $tab['service_id']);
                }
            }

            $ORX->add($orX1);
            if ($orX2) $ORX->add($orX2);
        }

        $queryBuilder->andWhere($ORX);
    }
}
