<?php

namespace App\Repository\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\SuiviOperationTache;
use App\Service\historiqueOperation\suivi\TacheSuiviOperation;
use Doctrine\ORM\EntityRepository;

/**
 * Tâches de correction issues du suivi des opérations (table suivi_operation_tache)
 */
class SuiviOperationTacheRepository extends EntityRepository
{
    // statuts d'une tâche pas encore terminée (filtre par défaut)
    public const STATUTS_OUVERTS = [
        TacheSuiviOperation::STATUT_A_TRIER,
        TacheSuiviOperation::STATUT_A_FAIRE,
        TacheSuiviOperation::STATUT_EN_COURS,
    ];

    /**
     * Tâches triées par urgence : à trier d'abord, puis priorité, régression, nombre d'occurrences et dernière occurrence
     *
     * @param array{statut?: ?string, effort?: ?string, priorite?: ?string, type?: ?string, assigne?: ?string, recherche?: ?string} $filtres
     *        statut : un statut, "OUVERTES" (à trier, à faire, en cours) ou vide pour toutes
     *        effort : un effort ou "NON_DEFINI"
     *
     * @return SuiviOperationTache[]
     */
    public function rechercher(array $filtres, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect("CASE WHEN t.statut = 'A_TRIER' THEN 0 ELSE 1 END AS HIDDEN ordreTri")
            ->addSelect("CASE t.priorite WHEN 'HAUTE' THEN 0 WHEN 'NORMALE' THEN 1 ELSE 2 END AS HIDDEN ordrePriorite")
            ->orderBy('ordreTri', 'ASC')
            ->addOrderBy('ordrePriorite', 'ASC')
            ->addOrderBy('t.estRegression', 'DESC')
            ->addOrderBy('t.nbOccurrences', 'DESC')
            ->addOrderBy('t.derniereOccurrence', 'DESC')
            ->setMaxResults($limit);

        $statut = $filtres['statut'] ?? 'OUVERTES';
        if ($statut === 'OUVERTES') {
            $qb->andWhere('t.statut IN (:statuts)')->setParameter('statuts', self::STATUTS_OUVERTS);
        } elseif ($statut) {
            $qb->andWhere('t.statut = :statut')->setParameter('statut', $statut);
        }

        $effort = $filtres['effort'] ?? null;
        if ($effort === 'NON_DEFINI') {
            $qb->andWhere('t.effort IS NULL');
        } elseif ($effort) {
            $qb->andWhere('t.effort = :effort')->setParameter('effort', $effort);
        }

        if (!empty($filtres['priorite'])) {
            $qb->andWhere('t.priorite = :priorite')->setParameter('priorite', $filtres['priorite']);
        }
        if (!empty($filtres['type'])) {
            $qb->andWhere('t.typeOperation = :type')->setParameter('type', $filtres['type']);
        }
        if (!empty($filtres['assigne'])) {
            $qb->andWhere('t.assigneA = :assigne')->setParameter('assigne', trim($filtres['assigne']));
        }
        if (!empty($filtres['recherche'])) {
            $qb->andWhere('t.etape LIKE :recherche OR t.dernierMessage LIKE :recherche OR t.dernierNumeroDocument LIKE :recherche OR t.cause LIKE :recherche OR t.correction LIKE :recherche')
                ->setParameter('recherche', '%' . trim($filtres['recherche']) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compteurs affichés en haut de l'onglet "Tâches"
     *
     * @return array{aTrier: int, rapides: int, longues: int, regressions: int}
     */
    public function compteurs(): array
    {
        $ligne = $this->getEntityManager()->getConnection()->fetchAssociative(
            "SELECT
                SUM(CASE WHEN statut = 'A_TRIER' THEN 1 ELSE 0 END) AS a_trier,
                SUM(CASE WHEN statut IN ('A_FAIRE', 'EN_COURS') AND effort = 'RAPIDE' THEN 1 ELSE 0 END) AS rapides,
                SUM(CASE WHEN statut IN ('A_FAIRE', 'EN_COURS') AND effort IN ('MOYEN', 'LONG') THEN 1 ELSE 0 END) AS longues,
                SUM(CASE WHEN statut IN ('A_TRIER', 'A_FAIRE', 'EN_COURS') AND est_regression = 1 THEN 1 ELSE 0 END) AS regressions
            FROM suivi_operation_tache"
        ) ?: [];

        return [
            'aTrier'      => (int) ($ligne['a_trier'] ?? 0),
            'rapides'     => (int) ($ligne['rapides'] ?? 0),
            'longues'     => (int) ($ligne['longues'] ?? 0),
            'regressions' => (int) ($ligne['regressions'] ?? 0),
        ];
    }

    /**
     * Tâches ouvertes assignées à l'utilisateur et sans échéance : notifications de la cloche,
     * qui disparaissent dès que l'utilisateur a planifié la tâche (échéance saisie)
     *
     * @return SuiviOperationTache[]
     */
    public function aPlanifier(string $nomUtilisateur): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assigneA = :nom')
            ->andWhere('t.echeance IS NULL')
            ->andWhere('t.statut IN (:statuts)')
            ->setParameter('nom', $nomUtilisateur)
            ->setParameter('statuts', self::STATUTS_OUVERTS)
            ->orderBy('t.dateModification', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * Rappel de la cloche : tâches ouvertes de l'utilisateur à faire aujourd'hui
     * (période début → échéance commencée ; sans début, échéance aujourd'hui) ou en retard
     *
     * @return SuiviOperationTache[]
     */
    public function aTraiter(string $nomUtilisateur): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assigneA = :nom')
            ->andWhere('t.echeance IS NOT NULL')
            ->andWhere('COALESCE(t.dateDebut, t.echeance) <= :aujourdhui')
            ->andWhere('t.statut IN (:statuts)')
            ->setParameter('nom', $nomUtilisateur)
            ->setParameter('aujourdhui', new \DateTime('today'), 'date')
            ->setParameter('statuts', self::STATUTS_OUVERTS)
            ->orderBy('t.echeance', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * Tâches résolues ou ignorées depuis 30 jours, pour les colonnes de fin du Kanban
     *
     * @return SuiviOperationTache[]
     */
    public function termineesRecentes(?string $assigne = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.statut IN (:statuts)')
            ->andWhere('t.dateModification >= :depuis')
            ->setParameter('statuts', [TacheSuiviOperation::STATUT_RESOLU, TacheSuiviOperation::STATUT_DEPLOYE, TacheSuiviOperation::STATUT_IGNORE])
            ->setParameter('depuis', new \DateTime('-30 days'))
            ->orderBy('t.dateModification', 'DESC')
            ->setMaxResults(100);

        if ($assigne) {
            $qb->andWhere('t.assigneA = :assigne')->setParameter('assigne', $assigne);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Tâches ouvertes avec une échéance, pour le Gantt de l'onglet "Planning" (groupées par intervenant)
     *
     * @return SuiviOperationTache[]
     */
    public function planifiees(?string $assigne = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.echeance IS NOT NULL')
            ->andWhere('t.statut IN (:statuts)')
            ->setParameter('statuts', self::STATUTS_OUVERTS)
            ->orderBy('t.assigneA', 'ASC')
            ->addOrderBy('t.dateDebut', 'ASC')
            ->addOrderBy('t.echeance', 'ASC');

        if ($assigne) {
            $qb->andWhere('t.assigneA = :assigne')->setParameter('assigne', $assigne);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Tâches indexées par id, pour les badges des opérations affichées dans le suivi
     *
     * @param int[] $ids
     * @return array<int, SuiviOperationTache>
     */
    public function parIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return [];
        }

        $taches = [];
        foreach ($this->findBy(['id' => $ids]) as $tache) {
            $taches[$tache->getId()] = $tache;
        }

        return $taches;
    }
}
