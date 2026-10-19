<?php

namespace App\Service\da;

use App\Constants\admin\ApplicationConstant;
use App\Service\security\SecurityService;
use Doctrine\ORM\QueryBuilder;

/**
 * Filtres agence / service de la liste DA (critères du formulaire + restriction selon les droits de l'utilisateur).
 */
class DaAgenceServiceFilterService
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function applyAgencyServiceFilters(QueryBuilder $qb, string $qbLabel, array $criteria)
    {
        if (!empty($criteria['agenceEmetteur'])) {
            $qb->andWhere("$qbLabel.agenceEmetteur = :agEmet")
                ->setParameter('agEmet', $criteria['agenceEmetteur']);
        }
        if (!empty($criteria['serviceEmetteur'])) {
            $qb->andWhere("$qbLabel.serviceEmetteur = :agServEmet")
                ->setParameter('agServEmet', $criteria['serviceEmetteur']);
        }


        if (!empty($criteria['agenceDebiteur'])) {
            $qb->andWhere("$qbLabel.agenceDebiteur = :agDebit")
                ->setParameter('agDebit', $criteria['agenceDebiteur'])
            ;
        }

        if (!empty($criteria['serviceDebiteur'])) {
            $qb->andWhere("$qbLabel.serviceDebiteur = :serviceDebiteur")
                ->setParameter('serviceDebiteur', $criteria['serviceDebiteur']);
        }

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->securityService->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE, "list_da");
        if (!$multisuccursale) {
            $this->conditionAgenceService($qb, $qbLabel);
        }
    }

    private function conditionAgenceService(QueryBuilder $queryBuilder, string $queryLabel)
    {
        $ORX = $queryBuilder->expr()->orX();

        // Agence et service par défaut
        $agenceIdUser = $this->securityService->getAgenceIdUser();
        $serviceIdUser = $this->securityService->getServiceIdUser();
        // Agence et service autoriser sur l'application DAP
        $agenceServiceAutorises = $this->securityService->getAgenceServices(ApplicationConstant::CODE_DAP);
        // Vérifier si l'utilisateur a le droit de voir la liste avec le débiteur
        $peutVoirListeAvecDebiteur = $this->securityService->verifierPermission(SecurityService::PERMISSION_AUTH_2, "list_da");

        // 1- Emetteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq("$queryLabel.agenceEmetteur", ':agEmetteur'),
                $queryBuilder->expr()->eq("$queryLabel.serviceEmetteur", ':servEmetteur')
            )
        );
        $queryBuilder->setParameter('agEmetteur', $agenceIdUser);
        $queryBuilder->setParameter('servEmetteur', $serviceIdUser);

        // 2- Debiteur du DOM : agence et service de l'utilisateur
        $ORX->add(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq("$queryLabel.agenceDebiteur", ':agDebiteur'),
                $queryBuilder->expr()->eq("$queryLabel.serviceDebiteur", ':servDebiteur')
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
                        $queryBuilder->expr()->eq("$queryLabel.agenceEmetteur", ':agEmetteur_' . $i),
                        $queryBuilder->expr()->eq("$queryLabel.serviceEmetteur", ':servEmetteur_' . $i)
                    )
                );
                $queryBuilder->setParameter('agEmetteur_' . $i, $tab['agence_id']);
                $queryBuilder->setParameter('servEmetteur_' . $i, $tab['service_id']);
                if ($orX2) {
                    $orX2->add(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq("$queryLabel.agenceDebiteur", ':agDebiteur_' . $i),
                            $queryBuilder->expr()->eq("$queryLabel.serviceDebiteur", ':servDebiteur_' . $i)
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
