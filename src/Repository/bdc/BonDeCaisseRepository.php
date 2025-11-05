<?php

namespace App\Repository\bdc;

use Doctrine\ORM\QueryBuilder;
use App\Entity\bdc\BonDeCaisse;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BonDeCaisseRepository extends EntityRepository
{
    public function filtres(QueryBuilder $queryBuilder, BonDeCaisse $bonDeCaisse, User $user): void
    {
        if (!in_array(1, $user->getRoleIds())) {
            $queryBuilder->andWhere('asi.agence_ips IN (:agencesAutorisees)')
                ->setParameter('agencesAutorisees', $user->getAgenceAutoriserCode())
                ->andWhere('asi.service_ips IN (:servicesAutorises)')
                ->setParameter('servicesAutorises', $user->getServiceAutoriserCode());
        }

        if ($bonDeCaisse->getNumeroDemande()) {
            $queryBuilder->andWhere('b.numeroDemande = :numeroDemande')
                ->setParameter('numeroDemande', $bonDeCaisse->getNumeroDemande());
        }

        // Filtrer par plage de date de demande
        $dateDemande = $bonDeCaisse->getDateDemande();
        $dateDemandeFin = $bonDeCaisse->getDateDemandeFin();

        if ($dateDemande && $dateDemandeFin) {
            $queryBuilder->andWhere('b.dateDemande BETWEEN :dateDemande AND :dateDemandeFin')
                ->setParameter('dateDemande', $dateDemande)
                ->setParameter('dateDemandeFin', $dateDemandeFin);
        } elseif ($dateDemande) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDemande')
                ->setParameter('dateDemande', $dateDemande);
        } elseif ($dateDemandeFin) {
            $queryBuilder->andWhere('b.dateDemande <= :dateDemandeFin')
                ->setParameter('dateDemandeFin', $dateDemandeFin);
        }

        // Filtrer par agence debiteur
        if ($bonDeCaisse->getAgenceDebiteur()) {
            $queryBuilder->andWhere('b.agenceDebiteur = :agenceDebiteur')
                ->setParameter('agenceDebiteur', $bonDeCaisse->getAgenceDebiteur());
        }

        // Filtrer par service debiteur
        if ($bonDeCaisse->getServiceDebiteur()) {
            $queryBuilder->andWhere('b.serviceDebiteur = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $bonDeCaisse->getServiceDebiteur());
        }

        // Filtrer par agence emetteur
        if ($bonDeCaisse->getAgenceEmetteur()) {
            $queryBuilder->andWhere('b.agenceEmetteur = :agenceEmetteur')
                ->setParameter('agenceEmetteur', $bonDeCaisse->getAgenceEmetteur());
        }

        // Filtrer par service emetteur
        if ($bonDeCaisse->getServiceEmetteur()) {
            $queryBuilder->andWhere('b.serviceEmetteur = :serviceEmetteur')
                ->setParameter('serviceEmetteur', $bonDeCaisse->getServiceEmetteur());
        }

        // Filtrer par caisse de retrait
        if ($bonDeCaisse->getCaisseRetrait()) {
            $queryBuilder->andWhere('b.caisseRetrait = :caisseRetrait')
                ->setParameter('caisseRetrait', $bonDeCaisse->getCaisseRetrait());
        }

        // Filtrer par type de paiement
        if ($bonDeCaisse->getTypePaiement()) {
            $queryBuilder->andWhere('b.typePaiement = :typePaiement')
                ->setParameter('typePaiement', $bonDeCaisse->getTypePaiement());
        }

        // Filtrer par retrait lié
        if ($bonDeCaisse->getRetraitLie()) {
            $queryBuilder->andWhere('b.retraitLie = :retraitLie')
                ->setParameter('retraitLie', $bonDeCaisse->getRetraitLie());
        }

        // Filtrer par statut
        if ($bonDeCaisse->getStatutDemande()) {
            $queryBuilder->andWhere('b.statutDemande = :statutDemande')
                ->setParameter('statutDemande', $bonDeCaisse->getStatutDemande());
        }

        // filtrer par nomValidateurFinal
        if ($bonDeCaisse->getNomValidateurFinal()) {
            $queryBuilder->andWhere('b.nomValidateurFinal LIKE :nomValidateurFinal')
                ->setParameter('nomValidateurFinal', '%' . $bonDeCaisse->getNomValidateurFinal() . '%git a');
        }
    }
    
    /**
     * Recupération des données paginée
     *
     * @param integer $page
     * @param integer $limit
     * @param BonDeCaisse $bonDeCaisse
     * @param User|null $user
     * @return array
     */
    public function findPaginatedAndFiltered(
        int $page,
        int $limit,
        BonDeCaisse $bonDeCaisse,
        ?User $user = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('b');

        $this->filtres($queryBuilder, $bonDeCaisse, $user);

        $query = $queryBuilder
            ->orderBy('b.id', 'DESC')
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

    /**
     * recupération des données à ajouter dans excel
     *
     * @param BonDeCaisse $bonDeCaisse
     * @return array
     */
    public function findAndFilteredExcel(BonDeCaisse $bonDeCaisse, ?User $user = null): array
    {
        $queryBuilder = $this->createQueryBuilder('b');

        $this->filtres($queryBuilder, $bonDeCaisse, $user);

        return $queryBuilder
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * recupérer tous les statuts 
     * 
     * cette methode recupère tous les statuts DISTINCT dans le table demande_bon_de_caisse
     * et le mettre en ordre ascendante
     * 
     * @return array
     */
    public function getStatut(): array
    {
        return $this->createQueryBuilder('b')
            ->select('DISTINCT b.statutDemande')
            ->orderBy('b.statutDemande', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
