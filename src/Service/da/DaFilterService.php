<?php

namespace App\Service\da;

use App\Constants\admin\ApplicationConstant;
use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Entity\dit\DemandeIntervention;
use App\Service\security\SecurityService;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\QueryBuilder;

class DaFilterService
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Applique les filtres dynamiques (recherche textuelle, urgence, demandeur, etc.)
     */
    public function applyDynamicFilters(QueryBuilder $qb, string $qbLabel, array $criteria, bool $estCdeFrn = false): void
    {
        if ($estCdeFrn) {
            $map = [
                'numDa'         => "$qbLabel.numeroDemandeApproMere",
                'numCde'        => "$qbLabel.numeroCde",
                'numFrn'        => "$qbLabel.numeroFournisseur",
                'frn'           => "$qbLabel.nomFournisseur",
                'niveauUrgence' => "$qbLabel.niveauUrgence",
                'demandeur'     => "$qbLabel.demandeur",
            ];
        } else {
            $map = [
                'numDa'         => "$qbLabel.numeroDemandeApproMere",
                'numCde'        => "$qbLabel.numeroCde",
                'demandeur'     => "$qbLabel.demandeur",
                'codeCentrale'  => "$qbLabel.codeCentrale",
                'niveauUrgence' => "$qbLabel.niveauUrgence",
            ];
        }

        foreach ($map as $key => $field) {
            if (!empty($criteria[$key])) {
                $val = $key === 'numDa' ? $this->supprimerQuatriemeLettrePD3($criteria[$key]) : $criteria[$key];
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $val);
            }
        }

        if (isset($criteria['numDit'])) {
            $qb->andWhere("$qbLabel.numeroOr = :numDit OR $qbLabel.numeroDemandeDit = :numDit")
                ->setParameter('numDit', $criteria['numDit']);
        }

        if (isset($criteria['typeAchat'])) {
            $qb->andWhere("$qbLabel.daTypeId = :typeAchat")
                ->setParameter('typeAchat', $criteria['typeAchat']);
        }

        if (empty($criteria['numDit']) && empty($criteria['numDa'])) {
            $joins = $qb->getDQLPart('join');
            $alreadyJoined = false;
            foreach ($joins as $rootAlias => $joinList) {
                foreach ($joinList as $join) {
                    if ($join->getAlias() === 'dit') {
                        $alreadyJoined = true;
                        break 2;
                    }
                }
            }

            if (!$alreadyJoined) {
                $qb->leftJoin("$qbLabel.dit", 'dit');
            }

            $qb->leftJoin('dit.idStatutDemande', 'statut')
                ->andWhere("$qbLabel.dit IS NULL OR statut.id NOT IN (:clotureStatut)")
                ->setParameter('clotureStatut', [
                    DemandeIntervention::STATUT_CLOTUREE_ANNULEE,
                    DemandeIntervention::STATUT_CLOTUREE_HORS_DELAI
                ]);
        }

        if (!empty($criteria['ref'])) {
            $qb->andWhere("$qbLabel.artRefp LIKE :ref")
                ->setParameter('ref', '%' . $criteria['ref'] . '%');
        }

        if (!empty($criteria['designation'])) {
            $qb->andWhere("$qbLabel.artDesi LIKE :designation")
                ->setParameter('designation', '%' . $criteria['designation'] . '%');
        }
    }

    /**
     * Applique les filtres de statuts (DA, BC, OR) et gère l'inclusion des clôturées
     */
    public function applyStatutsFilters(QueryBuilder $queryBuilder, string $qbLabel, array &$criteria, bool $estCdeFrn = false): void
    {
        $estAppro = $this->securityService->estAppro();
        $exprCloturee = $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq($qbLabel . '.statutDal', ':statutDaCloture'),
            $queryBuilder->expr()->eq($qbLabel . '.statutOr', ':statutOrValide'),
            $queryBuilder->expr()->eq($qbLabel . '.statutCde', ':statutBcTousLivres')
        );

        $fnSetClotureParams = function () use ($queryBuilder) {
            $queryBuilder->setParameter('statutDaCloture', StatutDaConstant::STATUT_CLOTUREE);
            $queryBuilder->setParameter('statutOrValide', StatutOrConstant::STATUT_VALIDE);
            $queryBuilder->setParameter('statutBcTousLivres', StatutBcConstant::STATUT_TOUS_LIVRES);
        };

        if (
            empty($criteria['numDit']) && empty($criteria['numDa']) && empty($criteria['numCde'])
            && empty($criteria['afficherCloturees'])
            && (!isset($criteria['statutDA']) || (!is_array($criteria['statutDA']) && !in_array($criteria['statutDA'], [StatutDaConstant::STATUT_TERMINER, StatutDaConstant::STATUT_CLOTUREE])))
        ) {
            $queryBuilder->andWhere($qbLabel . '.statutDal NOT IN (:statutDaFermer)')
                ->setParameter('statutDaFermer', [StatutDaConstant::STATUT_TERMINER, StatutDaConstant::STATUT_CLOTUREE], ArrayParameterType::STRING);
        }

        if ($estCdeFrn) {
            if (!empty($criteria['statutBC']) && !is_array($criteria['statutBC'])) {
                if ($criteria['statutBC'] === StatutBcConstant::BC_EN_COURS) {
                    if (empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($qbLabel . '.statutCde IN (:statutBcParam)')
                            ->setParameter('statutBcParam', StatutBcConstant::STATUT_BC_EN_COURS, ArrayParameterType::STRING);
                    } else {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX(
                            $qbLabel . '.statutCde IN (:statutBcParam)',
                            $exprCloturee
                        ))
                            ->setParameter('statutBcParam', StatutBcConstant::STATUT_BC_EN_COURS_CLOTURE, ArrayParameterType::STRING);
                        $fnSetClotureParams();
                    }
                } else {
                    if (empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($qbLabel . '.statutCde = :statutBcParam')
                            ->setParameter('statutBcParam', $criteria['statutBC']);
                    } else {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX(
                            $qbLabel . '.statutCde = :statutBcParam',
                            $exprCloturee
                        ))
                            ->setParameter('statutBcParam', $criteria['statutBC']);
                        $fnSetClotureParams();
                    }
                }
            }

            if (!empty($criteria['statutDA'])) {
                if (is_array($criteria['statutDA'])) {
                    $condNormal = $qbLabel . '.statutDal IN (:statutDaParam)';
                    if (!empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX($condNormal, $exprCloturee));
                        $fnSetClotureParams();
                    } else {
                        $queryBuilder->andWhere($condNormal);
                    }
                    $queryBuilder->setParameter('statutDaParam', $criteria['statutDA'], ArrayParameterType::STRING);
                } else {
                    if ($criteria['statutDA'] === StatutDaConstant::TRAITEMENT_APPRO) {
                        if (empty($criteria['afficherCloturees'])) {
                            $queryBuilder->andWhere($qbLabel . '.statutDal IN (:statutDaParam)')
                                ->setParameter('statutDaParam', StatutDaConstant::TRAITER_APPRO_LIST, ArrayParameterType::STRING);
                        } else {
                            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                                $qbLabel . '.statutDal IN (:statutDaParam)',
                                $exprCloturee
                            ))
                                ->setParameter('statutDaParam', StatutDaConstant::TRAITER_APPRO_LIST_CLOTURE, ArrayParameterType::STRING);
                            $fnSetClotureParams();
                        }
                    } else {
                        if (empty($criteria['afficherCloturees'])) {
                            $queryBuilder->andWhere($qbLabel . '.statutDal = :statutDaParam')
                                ->setParameter('statutDaParam', $criteria['statutDA']);
                        } else {
                            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                                $qbLabel . '.statutDal = :statutDaParam',
                                $exprCloturee
                            ))
                                ->setParameter('statutDaParam', $criteria['statutDA']);
                            $fnSetClotureParams();
                        }
                    }
                }
            }
        } else {
            if (
                !empty($criteria['afficherDaTraiter'])
                && empty($criteria['statutDA'])
                && empty($criteria['statutBC'])
                && empty($criteria['statutOR'])
                && empty($criteria['numDit'])
                && empty($criteria['numDa'])
                && empty($criteria['numCde'])
            ) {
                if ($estAppro) {
                    $criteria['statutDA'] = StatutDaConstant::TRAITER_APPRO_LIST;
                    $criteria['statutBC'] = StatutBcConstant::TRAITER_APPRO_LIST;
                } else {
                    $criteria['statutDA'] = StatutDaConstant::TRAITER_AUTRES_LIST;
                }
            }

            if (!empty($criteria['statutDA']) && !empty($criteria['statutBC']) && is_array($criteria['statutDA']) && is_array($criteria['statutBC'])) {
                $condNormal = $queryBuilder->expr()->orX(
                    $qbLabel . '.statutDal IN (:statutDaParam)',
                    $qbLabel . '.statutCde IN (:statutBcParam)'
                );

                if (!empty($criteria['afficherCloturees'])) {
                    $queryBuilder->andWhere($queryBuilder->expr()->orX($condNormal, $exprCloturee));
                    $fnSetClotureParams();
                } else {
                    $queryBuilder->andWhere($condNormal);
                }
                $queryBuilder->setParameter('statutDaParam', $criteria['statutDA'], ArrayParameterType::STRING)
                    ->setParameter('statutBcParam', $criteria['statutBC'], ArrayParameterType::STRING);
            } elseif (!empty($criteria['statutDA'])) {
                if (is_array($criteria['statutDA'])) {
                    $condNormal = $qbLabel . '.statutDal IN (:statutDaParam)';
                    if (!empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX($condNormal, $exprCloturee));
                        $fnSetClotureParams();
                    } else {
                        $queryBuilder->andWhere($condNormal);
                    }
                    $queryBuilder->setParameter('statutDaParam', $criteria['statutDA'], ArrayParameterType::STRING);
                } else {
                    if ($criteria['statutDA'] === StatutDaConstant::TRAITEMENT_APPRO) {
                        if (empty($criteria['afficherCloturees'])) {
                            $queryBuilder->andWhere($qbLabel . '.statutDal IN (:statutDaParam)')
                                ->setParameter('statutDaParam', StatutDaConstant::TRAITER_APPRO_LIST, ArrayParameterType::STRING);
                        } else {
                            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                                $qbLabel . '.statutDal IN (:statutDaParam)',
                                $exprCloturee
                            ))
                                ->setParameter('statutDaParam', StatutDaConstant::TRAITER_APPRO_LIST_CLOTURE, ArrayParameterType::STRING);
                            $fnSetClotureParams();
                        }
                    } else {
                        if (empty($criteria['afficherCloturees'])) {
                            $queryBuilder->andWhere($qbLabel . '.statutDal = :statutDaParam')
                                ->setParameter('statutDaParam', $criteria['statutDA']);
                        } else {
                            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                                $qbLabel . '.statutDal = :statutDaParam',
                                $exprCloturee
                            ))
                                ->setParameter('statutDaParam', $criteria['statutDA']);
                            $fnSetClotureParams();
                        }
                    }
                }
            }

            if (!empty($criteria['statutBC']) && !is_array($criteria['statutBC'])) {
                if ($criteria['statutBC'] === StatutBcConstant::BC_EN_COURS) {
                    if (empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($qbLabel . '.statutCde IN (:statutBcParam)')
                            ->setParameter('statutBcParam', StatutBcConstant::STATUT_BC_EN_COURS, ArrayParameterType::STRING);
                    } else {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX(
                            $qbLabel . '.statutCde IN (:statutBcParam)',
                            $exprCloturee
                        ))
                            ->setParameter('statutBcParam', StatutBcConstant::STATUT_BC_EN_COURS_CLOTURE, ArrayParameterType::STRING);
                        $fnSetClotureParams();
                    }
                } else {
                    if (empty($criteria['afficherCloturees'])) {
                        $queryBuilder->andWhere($qbLabel . '.statutCde = :statutBcParam')
                            ->setParameter('statutBcParam', $criteria['statutBC']);
                    } else {
                        $queryBuilder->andWhere($queryBuilder->expr()->orX(
                            $qbLabel . '.statutCde = :statutBcParam',
                            $exprCloturee
                        ))
                            ->setParameter('statutBcParam', $criteria['statutBC']);
                        $fnSetClotureParams();
                    }
                }
            }

            if (!empty($criteria['statutOR']) && !is_array($criteria['statutOR'])) {
                if (empty($criteria['afficherCloturees'])) {
                    $queryBuilder->andWhere($qbLabel . '.statutOr = :statutOrParam')
                        ->setParameter('statutOrParam', $criteria['statutOR']);
                } else {
                    $queryBuilder->andWhere($queryBuilder->expr()->orX(
                        $qbLabel . '.statutOr = :statutOrParam',
                        $exprCloturee
                    ))
                        ->setParameter('statutOrParam', $criteria['statutOR']);
                    $fnSetClotureParams();
                }
            }
        }
    }

    /**
     * Applique les filtres de date (date fin souhaitée, date création, date planning OR)
     */
    public function applyDateFilters(QueryBuilder $qb, string $qbLabel, array $criteria, bool $estCdeFrn = false): void
    {
        if ($estCdeFrn) {
            if (!empty($criteria['dateDebutfinSouhaite']) && $criteria['dateDebutfinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }
            if (!empty($criteria['dateFinFinSouhaite']) && $criteria['dateFinFinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }
            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }
        } else {
            if (!empty($criteria['dateDebutfinSouhaite'])) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }
            if (!empty($criteria['dateFinFinSouhaite'])) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }
            if (!empty($criteria['dateDebutCreation'])) {
                $qb->andWhere($qbLabel . '.dateDemande >= :dateDemandeDebut')
                    ->setParameter('dateDemandeDebut', $criteria['dateDebutCreation']);
            }
            if (!empty($criteria['dateFinCreation'])) {
                $qb->andWhere($qbLabel . '.dateDemande <= :dateDemandeFin')
                    ->setParameter('dateDemandeFin', $criteria['dateFinCreation']);
            }
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }
            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }
        }
    }

    public function applyAgencyServiceFilters($qb, string $qbLabel, array $criteria)
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
        $multisuccursale = $this->securityService->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);
        if (!$multisuccursale) {
            $this->conditionAgenceService($qb, $qbLabel);
        }
    }

    private function conditionAgenceService($queryBuilder, string $queryLabel)
    {
        $ORX = $queryBuilder->expr()->orX();

        // Agence et service par défaut
        $agenceIdUser = $this->securityService->getAgenceIdUser();
        $serviceIdUser = $this->securityService->getServiceIdUser();
        // Agence et service autoriser sur l'application DAP
        $agenceServiceAutorises = $this->securityService->getAgenceServices(ApplicationConstant::CODE_DAP);
        // Vérifier si l'utilisateur a le droit de voir la liste avec le débiteur
        $peutVoirListeAvecDebiteur = $this->securityService->verifierPermission(SecurityService::PERMISSION_AUTH_2);

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

    /**
     * Gère l'ordre de tri (ORDER BY)
     */
    public function handleOrderBy(QueryBuilder $qb, string $qbLabel, array $criteria, bool $aggregation = false): void
    {
        $allowedDirs = ['ASC', 'DESC'];

        if ($criteria && !empty($criteria['sortNbJours'])) {
            $orderDir = strtoupper($criteria['sortNbJours']);
            if (!in_array($orderDir, $allowedDirs, true)) $orderDir = 'DESC';

            if ($aggregation) {
                $orderFunc = $orderDir === 'DESC' ? 'MAX' : 'MIN';
                $qb->orderBy("$orderFunc($qbLabel.joursDispo)", $orderDir);
            } else {
                $qb->orderBy("$qbLabel.joursDispo", $orderDir);
            }
        }

        $dateDemandeExpr = $aggregation ? "MAX($qbLabel.dateDemande)" : "$qbLabel.dateDemande";
        $qb->addOrderBy($dateDemandeExpr, 'DESC');
    }

    /**
     * Supprime la 4ème lettre (P, p, D, d) de la chaîne si elle remplit certaines conditions
     */
    private function supprimerQuatriemeLettrePD3($chaine)
    {
        if (strlen($chaine) > 11 && isset($chaine[3])) {
            $lettresASupprimer = ['P', 'p', 'D', 'd'];
            if (in_array($chaine[3], $lettresASupprimer, true)) {
                $chaine = substr($chaine, 0, 3) . substr($chaine, 4);
            }
        }
        return $chaine;
    }
}
