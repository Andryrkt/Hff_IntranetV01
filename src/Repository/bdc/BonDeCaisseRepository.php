<?php

namespace App\Repository\bdc;

use Doctrine\ORM\QueryBuilder;
use App\Entity\bdc\BonDeCaisse;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BonDeCaisseRepository extends EntityRepository
{
    public function filtres(QueryBuilder $queryBuilder, BonDeCaisse $bonDeCaisse, string $agenceCodeUser, string $serviceCodeUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur, $multisuccursale): void
    {
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

        if (!$multisuccursale) {
            // Condition sur les couples agences-services
            $this->conditionAgenceService($queryBuilder, $agenceCodeUser, $serviceCodeUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);
        }
    }

    private function conditionAgenceService($queryBuilder, string $agenceCodeUser, string $serviceCodeUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $ORX = $queryBuilder->expr()->orX();

        // 1- Emetteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('b.agenceEmetteur', ':agEmetteur'),
                $queryBuilder->expr()->eq('b.serviceEmetteur', ':servEmetteur')
            )
        );
        $queryBuilder->setParameter('agEmetteur', $agenceCodeUser);
        $queryBuilder->setParameter('servEmetteur', $serviceCodeUser);

        // 2- Debiteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('b.agenceDebiteur', ':agDebiteur'),
                $queryBuilder->expr()->eq('b.serviceDebiteur', ':servDebiteur')
            )
        );
        $queryBuilder->setParameter('agDebiteur', $agenceCodeUser);
        $queryBuilder->setParameter('servDebiteur', $serviceCodeUser);

        // 3- Emetteur et Débiteur : agence et service autorisés du profil
        if (!empty($agenceServiceAutorises)) {
            $orX1 = $queryBuilder->expr()->orX(); // Pour émetteur
            $orX2 = $peutVoirListeAvecDebiteur ? $queryBuilder->expr()->orX() : null; // Pour débiteur : n'autoriser que si le profil peut voir la liste avec le débiteur
            foreach ($agenceServiceAutorises as $i => $tab) {
                $orX1->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('b.agenceEmetteur', ':agEmetteur_' . $i),
                        $queryBuilder->expr()->eq('b.serviceEmetteur', ':servEmetteur_' . $i)
                    )
                );
                $queryBuilder->setParameter('agEmetteur_' . $i, $tab['agence_code']);
                $queryBuilder->setParameter('servEmetteur_' . $i, $tab['service_code']);
                if ($orX2) {
                    $orX2->add(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('b.agenceDebiteur', ':agDebiteur_' . $i),
                            $queryBuilder->expr()->eq('b.serviceDebiteur', ':servDebiteur_' . $i)
                        )
                    );
                    $queryBuilder->setParameter('agDebiteur_' . $i, $tab['agence_code']);
                    $queryBuilder->setParameter('servDebiteur_' . $i, $tab['service_code']);
                }
            }

            $ORX->add($orX1);
            if ($orX2) $ORX->add($orX2);
        }

        $queryBuilder->andWhere($ORX);
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
        string $agenceCodeUser,
        string $serviceCodeUser,
        array $agenceServiceAutorises,
        bool $peutVoirListeAvecDebiteur,
        bool $multisuccursale
    ): array {
        $queryBuilder = $this->createQueryBuilder('b');

        $this->filtres($queryBuilder, $bonDeCaisse, $agenceCodeUser, $serviceCodeUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur, $multisuccursale);

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
    public function findAndFilteredExcel(
        BonDeCaisse $bonDeCaisse,
        string $agenceCodeUser,
        string $serviceCodeUser,
        array $agenceServiceAutorises,
        bool $peutVoirListeAvecDebiteur,
        bool $multisuccursale
    ): array {
        $queryBuilder = $this->createQueryBuilder('b');

        $this->filtres($queryBuilder, $bonDeCaisse, $agenceCodeUser, $serviceCodeUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur, $multisuccursale);

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
