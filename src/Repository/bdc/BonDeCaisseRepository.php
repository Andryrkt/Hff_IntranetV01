<?php

namespace App\Repository\bdc;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\bdc\BonDeCaisse;

class BonDeCaisseRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(
        int $page,
        int $limit,
        BonDeCaisse $bonDeCaisse
    ): array {
        $queryBuilder = $this->createQueryBuilder('b');

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

    public function findAndFilteredExcel(BonDeCaisse $bonDeCaisse, array $options): array
    {
        $queryBuilder = $this->createQueryBuilder('b');

        // Appliquer les mêmes filtres que pour la pagination
        if ($bonDeCaisse->getNumeroDemande()) {
            $queryBuilder->andWhere('b.numeroDemande = :numeroDemande')
                ->setParameter('numeroDemande', $bonDeCaisse->getNumeroDemande());
        }

        // Filtrer par plage de date de demande
        if ($bonDeCaisse->getDateDemande()) {
            $dateDemandeFin = $options['dateDemandeFin'] ?? null;

            if ($dateDemandeFin) {
                $queryBuilder->andWhere('b.dateDemande BETWEEN :dateDemande AND :dateDemandeFin')
                    ->setParameter('dateDemande', $bonDeCaisse->getDateDemande())
                    ->setParameter('dateDemandeFin', $dateDemandeFin);
            } else {
                $queryBuilder->andWhere('b.dateDemande = :dateDemande')
                    ->setParameter('dateDemande', $bonDeCaisse->getDateDemande());
            }
        }

        // Autres filtres identiques à findPaginatedAndFiltered
        // ...

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
