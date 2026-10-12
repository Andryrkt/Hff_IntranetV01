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

        // seulement pour les DA type demande d'approvisionnement via OR
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
        $this->exclureDaFermees($queryBuilder, $qbLabel, $criteria);

        if ($estCdeFrn) {
            $this->filtrerStatutBc($queryBuilder, $qbLabel, $criteria);
            $this->filtrerStatutDa($queryBuilder, $qbLabel, $criteria);
            return;
        }

        if (
            !empty($criteria['afficherDaTraiter'])
            && empty($criteria['statutDA'])
            && empty($criteria['statutBC'])
            && empty($criteria['statutOR'])
            && empty($criteria['numDit'])
            && empty($criteria['numDa'])
            && empty($criteria['numCde'])
        ) {
            if ($this->securityService->estAppro()) {
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
            $this->andWhereOuCloturee($queryBuilder, $qbLabel, $condNormal, !empty($criteria['afficherCloturees']));
            $queryBuilder->setParameter('statutDaParam', $criteria['statutDA'], ArrayParameterType::STRING)
                ->setParameter('statutBcParam', $criteria['statutBC'], ArrayParameterType::STRING);
        } else {
            $this->filtrerStatutDa($queryBuilder, $qbLabel, $criteria);
        }

        $this->filtrerStatutBc($queryBuilder, $qbLabel, $criteria);
        $this->filtrerStatutOr($queryBuilder, $qbLabel, $criteria);
    }

    /** Sans recherche précise ni filtre « clôturées » / statut terminé ou clôturé, exclut les DA terminées et clôturées */
    private function exclureDaFermees(QueryBuilder $queryBuilder, string $qbLabel, array $criteria): void
    {
        if (
            empty($criteria['numDit']) && empty($criteria['numDa']) && empty($criteria['numCde'])
            && empty($criteria['afficherCloturees'])
            && (!isset($criteria['statutDA']) || (!is_array($criteria['statutDA']) && !in_array($criteria['statutDA'], [StatutDaConstant::STATUT_TERMINER, StatutDaConstant::STATUT_CLOTUREE])))
        ) {
            $queryBuilder->andWhere($qbLabel . '.statutDal NOT IN (:statutDaFermer)')
                ->setParameter('statutDaFermer', [StatutDaConstant::STATUT_TERMINER, StatutDaConstant::STATUT_CLOTUREE], ArrayParameterType::STRING);
        }
    }

    private function filtrerStatutDa(QueryBuilder $queryBuilder, string $qbLabel, array $criteria): void
    {
        if (empty($criteria['statutDA'])) return;

        $statutDa = $criteria['statutDA'];
        $cloturees = !empty($criteria['afficherCloturees']);

        if (is_array($statutDa)) {
            $cond = $qbLabel . '.statutDal IN (:statutDaParam)';
            $valeur = $statutDa;
        } elseif ($statutDa === StatutDaConstant::TRAITEMENT_APPRO) {
            $cond = $qbLabel . '.statutDal IN (:statutDaParam)';
            $valeur = $cloturees ? StatutDaConstant::TRAITER_APPRO_LIST_CLOTURE : StatutDaConstant::TRAITER_APPRO_LIST;
        } else {
            $cond = $qbLabel . '.statutDal = :statutDaParam';
            $valeur = $statutDa;
        }

        $this->andWhereOuCloturee($queryBuilder, $qbLabel, $cond, $cloturees);
        $queryBuilder->setParameter('statutDaParam', $valeur, is_array($valeur) ? ArrayParameterType::STRING : null);
    }

    private function filtrerStatutBc(QueryBuilder $queryBuilder, string $qbLabel, array $criteria): void
    {
        if (empty($criteria['statutBC']) || is_array($criteria['statutBC'])) return;

        $statutBc = $criteria['statutBC'];
        $cloturees = !empty($criteria['afficherCloturees']);

        if ($statutBc === StatutBcConstant::BC_EN_COURS) {
            $cond = $qbLabel . '.statutCde IN (:statutBcParam)';
            $valeur = $cloturees ? StatutBcConstant::STATUT_BC_EN_COURS_CLOTURE : StatutBcConstant::STATUT_BC_EN_COURS;
        } else {
            $cond = $qbLabel . '.statutCde = :statutBcParam';
            $valeur = $statutBc;
        }

        $this->andWhereOuCloturee($queryBuilder, $qbLabel, $cond, $cloturees);
        $queryBuilder->setParameter('statutBcParam', $valeur, is_array($valeur) ? ArrayParameterType::STRING : null);
    }

    private function filtrerStatutOr(QueryBuilder $queryBuilder, string $qbLabel, array $criteria): void
    {
        if (empty($criteria['statutOR']) || is_array($criteria['statutOR'])) return;

        $this->andWhereOuCloturee($queryBuilder, $qbLabel, $qbLabel . '.statutOr = :statutOrParam', !empty($criteria['afficherCloturees']));
        $queryBuilder->setParameter('statutOrParam', $criteria['statutOR']);
    }

    /** Ajoute la condition ; avec $cloturees, la condition est étendue aux DA clôturées (DA clôturée + OR validé + BC tous livrés) */
    private function andWhereOuCloturee(QueryBuilder $queryBuilder, string $qbLabel, $cond, bool $cloturees): void
    {
        if (!$cloturees) {
            $queryBuilder->andWhere($cond);
            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->orX($cond, $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq($qbLabel . '.statutDal', ':statutDaCloture'),
            $queryBuilder->expr()->eq($qbLabel . '.statutOr', ':statutOrValide'),
            $queryBuilder->expr()->eq($qbLabel . '.statutCde', ':statutBcTousLivres')
        )))
            ->setParameter('statutDaCloture', StatutDaConstant::STATUT_CLOTUREE)
            ->setParameter('statutOrValide', StatutOrConstant::STATUT_VALIDE)
            ->setParameter('statutBcTousLivres', StatutBcConstant::STATUT_TOUS_LIVRES);
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
