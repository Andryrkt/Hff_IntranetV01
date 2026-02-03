<?php

namespace App\Repository\da;

use App\Entity\da\DaAfficher;
use Doctrine\ORM\QueryBuilder;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;

class DaAfficherRepository extends EntityRepository
{
    /**
     *  Récupère les dernières versions pour une demande d'approvisionnement (DA) donnée.
     *
     * @param string $numeroDemandeAppro
     */
    public function getLastDaAfficher(string $numeroDemandeAppro)
    {
        // Étape 1 : récupérer la version max pour ce numero_DA
        $maxVersion = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :num')
            ->setParameter('num', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult(); // Renvoie null si aucune ligne

        if ($maxVersion === null) {
            return [];
        } else {
            // Étape 2 : récupérer tous les enregistrements correspondant
            return $this->createQueryBuilder('d')
                ->where('d.numeroDemandeAppro = :num')
                ->andWhere('d.numeroVersion = :version')
                ->setParameters([
                    'num'     => $numeroDemandeAppro,
                    'version' => $maxVersion,
                ])
                ->getQuery()
                ->getResult();
        }
    }

    /**
     * @param string $numeroDemandeAppro
     * @param string $numeroCde
     */
    public function getDateLivraisonPrevue(string $numeroDemandeAppro, string $numeroCde)
    {
        $maxVersion = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :num')
            ->setParameter('num', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult(); // Renvoie null si aucune ligne

        if ($maxVersion === null) {
            return [];
        } else {
            return $this->createQueryBuilder('d')
                ->select('DISTINCT(d.dateLivraisonPrevue)')
                ->where('d.numeroDemandeAppro = :num')
                ->andWhere('d.numeroCde = :numCde')
                ->andWhere('d.numeroVersion = :version')
                ->andWhere('d.dateLivraisonPrevue IS NOT NULL')
                ->setParameters([
                    'num'     => $numeroDemandeAppro,
                    'numCde'  => $numeroCde,
                    'version' => $maxVersion,
                ])
                ->getQuery()
                ->getSingleScalarResult();
        }
    }

    public function markAsDeletedByNumeroLigne(string $numeroDemandeAppro, array $numeroLignes, string $userName, bool $allVersions = false): void
    {
        if (empty($numeroLignes)) return; // rien à faire

        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.deleted', ':deleted')
            ->set('d.deletedBy', ':deletedBy')
            ->where('d.numeroDemandeAppro = :num')
            ->andWhere('d.numeroLigne IN (:lines)')
            ->setParameters([
                'num'       => $numeroDemandeAppro,
                'deleted'   => true,
                'deletedBy' => $userName,
                'lines'     => $numeroLignes,
            ]);

        // Si $allVersions = false, on cible uniquement la dernière version
        if (!$allVersions) {
            // Récupérer le numéro de la dernière version
            $lastVersion = $this->createQueryBuilder('d')
                ->select('MAX(d.numeroVersion)')
                ->where('d.numeroDemandeAppro = :num')
                ->setParameter('num', $numeroDemandeAppro)
                ->getQuery()
                ->getSingleScalarResult();

            // Si aucune version n'existe, on arrête
            if ($lastVersion === null) return;

            // Ajouter la condition sur la version
            $qb->andWhere('d.numeroVersion = :version')
                ->setParameter('version', $lastVersion);
        }

        // Exécuter la requête
        $qb->getQuery()->execute();
    }

    public function markAsDeletedByListId(array $ids, string $userName): void
    {
        if (empty($ids)) return; // rien à faire

        try {
            $this->createQueryBuilder('d')
                ->update()
                ->set('d.deleted', ':deleted')
                ->set('d.deletedBy', ':deletedBy')
                ->Where('d.id IN (:ids)')
                ->setParameters([
                    'deleted'   => true,
                    'deletedBy' => $userName,
                    'ids'       => $ids,
                ])
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     *  Récupère le numéro de version maximum pour une demande d'approvisionnement (DA) donnée.
     *
     * @param string $numeroDemandeAppro
     */
    public function getNumeroVersionMax(string $numeroDemandeAppro)
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return 0;
        }
        return $numeroVersionMax;
    }

    /**
     *  Récupère le numéro de version maximum pour une numero commande (Cde) donnée.
     *
     * @param string $numeroCde
     * @return int
     */
    public function getNumeroVersionMaxCde(string $numeroCde): int
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('DISTINCT MAX(d.numeroVersion)')
            ->where('d.numeroCde = :numCde')
            ->setParameter('numCde', $numeroCde)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return 0;
        }
        return $numeroVersionMax;
    }

    /**
     *  Récupère le numéro de version maximum pour une numero demande d'intervention (DIT) donnée.
     *
     * @param string $numeroDit
     * @return int
     */
    public function getNumeroVersionMaxDit(?string $numeroDit): int
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('DISTINCT MAX(d.numeroVersion)')
            ->where('d.numeroDemandeDit = :numDit')
            ->setParameter('numDit', $numeroDit)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return 0;
        }
        return $numeroVersionMax;
    }

    public function getDalider($numeroVersion, $numeroDemandeDit, $reference, $designation, $criteria = [])
    {
        $dalider =  $this->createQueryBuilder('d')
            ->where('d.numeroVersion = :version')
            ->andWhere('d.numeroDemandeDit = :numDit')
            ->andWhere('d.artRefp = :ref')
            ->andWhere('d.artDesi = :desi')
            ->setParameters([
                'version' => $numeroVersion,
                'ref' => $reference,
                'desi' => $designation,
                'numDit' => $numeroDemandeDit
            ]);
        if (empty($criteria['numDa'])) {
            $dalider->andWhere('d.statutDal != :statut')
                ->setParameter('statut', 'TERMINER');
        }

        // $query = $dalider->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }
        return $dalider
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getSumQteDemEtLivrer(string $numDa): array
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult();
        if ($numeroVersionMax === null) {
            return [
                'qteDem' => 0,
                'qteLivrer' => 0
            ];
        }
        $qb = $this->createQueryBuilder('d')
            ->select('SUM(d.qteDem) as qteDem, SUM(d.qteLivrer) as qteLivrer')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->andWhere('d.numeroVersion = :numVersion')
            ->setParameter('numVersion', $numeroVersionMax);

        return $qb->getQuery()->getSingleResult();
    }

    public function getConstructeurRefDesi(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select("CONCAT(d.artConstp, '_', d.artRef, '_', d.artDesi) AS refDesi")
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'refDesi');
    }

    /**
     * Récupère le dernier version de DA pour la liste cde frn
     * Regroupé par DA mère pour la pagination
     * @param array $criteria
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function findValidatedPaginatedDas(?array $criteria = [], int $page, int $limit): array
    {
        $criteria = $criteria ?? [];

        // -------------------------------------
        // 1. Sous-requête : versions maximales par DA
        // -------------------------------------
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select(
            'd.numeroDemandeApproMere',
            'd.numeroDemandeAppro',
            'MAX(d.numeroVersion) as maxVersion'
        )
            ->from(DaAfficher::class, 'd')
            ->groupBy('d.numeroDemandeApproMere, d.numeroDemandeAppro');

        // Liste des statuts OR (statut depuis DW pour les DA directs)
        $statutOrs = [
            DitOrsSoumisAValidation::STATUT_VALIDE,
            DemandeAppro::STATUT_DW_VALIDEE
        ];

        // Liste des exceptions pour lesquelles statutOr n'est pas requis
        $exceptions = [
            'DAP25079981'
        ];
        // Condition générique sur statutOr avec exceptions
        $orCondition = $subQb->expr()->orX(
            $subQb->expr()->in('d.statutOr', ':statutOrs'),
            $subQb->expr()->in('d.numeroDemandeAppro', ':exceptions')
        );

        $subQb->andWhere($orCondition);

        // Paramètres communs
        $subQb->setParameter('statutOrs', $statutOrs)
            ->setParameter('exceptions', $exceptions);

        $statutDas = [
            DemandeAppro::STATUT_CLOTUREE,
            DemandeAppro::STATUT_VALIDE
        ];
        $subQb->andWhere('d.statutDal IN (:statutDal)')
            ->setParameter('statutDal', $statutDas);

        $this->applyDynamicFilters($subQb, "d", $criteria, true);
        $this->applyStatutsFilters($subQb, "d", $criteria, true);
        $this->applyDateFilters($subQb, "d", $criteria, true);

        // ---------------------------------
        // 2. Compter distinctement les DA mères
        // ---------------------------------
        $countQb = clone $subQb;
        $countQb->resetDQLPart('select');
        $countQb->resetDQLPart('orderBy');
        $countQb->resetDQLPart('groupBy');
        $countQb->select('COUNT(DISTINCT d.numeroDemandeApproMere)');

        $totalItems = (int) $countQb->getQuery()
            ->getSingleScalarResult();

        $lastPage = (int) ceil($totalItems / $limit);

        // ---------------------------
        // 3. Paginer par DA mère
        // ---------------------------
        // D'abord récupérer les DA mères paginées
        $paginatedMeresQb = clone $subQb;
        $paginatedMeresQb->resetDQLPart('select');
        $paginatedMeresQb->resetDQLPart('groupBy');

        // ✅ Sélectionner les colonnes nécessaires pour le ORDER BY (pour SQL Server)
        $paginatedMeresQb->select('d.numeroDemandeApproMere')
            ->addSelect('MAX(d.dateDemande) as maxDateDemande')
            ->addSelect('MAX(d.numeroFournisseur) as maxNumeroFournisseur')
            ->addSelect('MAX(d.numeroCde) as maxNumeroCde')
            ->groupBy('d.numeroDemandeApproMere');

        // Gestion du tri personnalisé
        if (!empty($criteria['sortNbJours'])) {
            $orderDir = strtoupper($criteria['sortNbJours']);
            if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
                $orderDir = 'DESC';
            }
            $orderFunc = $orderDir === 'DESC' ? 'MAX' : 'MIN';
            $paginatedMeresQb->orderBy("$orderFunc(d.joursDispo)", $orderDir);
        } else {
            $paginatedMeresQb->orderBy('maxDateDemande', 'DESC');
        }

        $paginatedMeresQb
            ->addOrderBy('maxNumeroFournisseur', 'DESC')
            ->addOrderBy('maxNumeroCde', 'DESC')
            ->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginatedMeres = array_column(
            $paginatedMeresQb->getQuery()->getArrayResult(),
            'numeroDemandeApproMere'
        );

        if (empty($paginatedMeres)) {
            return [
                'data'        => [],
                'totalItems'  => 0,
                'currentPage' => $page,
                'lastPage'    => 0,
            ];
        }

        // Maintenant récupérer toutes les versions max pour ces DA mères
        $versionsQb = clone $subQb;
        $versionsQb->andWhere('d.numeroDemandeApproMere IN (:paginatedMeres)')
            ->setParameter('paginatedMeres', $paginatedMeres);

        // Gestion du tri personnalisé pour versionsQb
        if (!empty($criteria['sortNbJours'])) {
            $orderDir = strtoupper($criteria['sortNbJours']);
            if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
                $orderDir = 'DESC';
            }
            $orderFunc = $orderDir === 'DESC' ? 'MAX' : 'MIN';
            $versionsQb->orderBy("$orderFunc(d.joursDispo)", $orderDir);
        } else {
            $versionsQb->orderBy('MAX(d.dateDemande)', 'DESC');
        }

        $versionsQb
            ->addOrderBy('MAX(d.numeroFournisseur)', 'DESC')
            ->addOrderBy('MAX(d.numeroCde)', 'DESC')
            ->addOrderBy('MAX(d.numeroDemandeApproMere)', 'DESC')
            ->addOrderBy('MAX(d.numeroDemandeAppro)', 'DESC');

        $latestVersions = $versionsQb->getQuery()
            ->getArrayResult();

        // ------------------------------------
        // 4. Construire la requête principale
        // ------------------------------------
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d')
            ->from(DaAfficher::class, 'd')
            ->where($qb->expr()->orX(
                'd.statutCde != :statutPasDansOr',
                'd.statutCde IS NULL'
            )) // enlever les lignes qui ont le statut PAS DANS OR
            ->andWhere('d.deleted = 0')
            ->setParameter('statutPasDansOr', DaSoumissionBc::STATUT_PAS_DANS_OR);

        // Condition sur les DA mères paginées
        $qb->andWhere('d.numeroDemandeApproMere IN (:paginatedMeres)')
            ->setParameter('paginatedMeres', $paginatedMeres);

        $qb->andWhere('d.statutDal IN (:statutDal)')
            ->setParameter('statutDal', $statutDas);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->in('d.statutOr', ':statutOrsValide'),
                $qb->expr()->in('d.numeroDemandeAppro', ':exceptions')
            )
        )
            ->setParameter('statutOrsValide', $statutOrs)
            ->setParameter('exceptions', $exceptions);

        // Condition sur les versions maximales (à partir de la sous-requête)
        $orX = $qb->expr()->orX();
        foreach ($latestVersions as $i => $version) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('d.numeroDemandeAppro', ':numDa' . $i),
                    $qb->expr()->eq('d.numeroVersion', ':maxVer' . $i)
                )
            );
            $qb->setParameter('numDa' . $i, $version['numeroDemandeAppro']);
            $qb->setParameter('maxVer' . $i, $version['maxVersion']);
        }
        $qb->andWhere($orX);

        $this->applyDynamicFilters($qb, "d", $criteria, true);
        $this->applyStatutsFilters($qb, "d", $criteria, true);
        $this->applyDateFilters($qb, "d", $criteria, true);

        // Triage selon le filtre choisi par l'utilisateur
        if (!empty($criteria['sortNbJours'])) {
            $qb->orderBy('d.joursDispo', $criteria['sortNbJours']);
        } else {
            // Ordre final
            $qb->orderBy('d.dateDemande', 'DESC')
                ->addOrderBy('d.numeroFournisseur', 'DESC')
                ->addOrderBy('d.numeroCde', 'DESC');
        }

        $qb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC');

        // ----------------------
        // 5. Retour
        // ----------------------
        return [
            'data'        => $qb->getQuery()->getResult(),
            'totalItems'  => $totalItems,   // ✅ Compte correct des DA mères avec filtres
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }


    /**
     * Fonction publique : renvoie les DA paginés avec filtres appliqués uniquement sur les dernières versions
     */
    public function findPaginatedAndFilteredDA(
        User $user,
        array $criteria,
        int $idAgenceUser,
        bool $estAppro,
        bool $estAtelier,
        bool $estAdmin,
        int $page,
        int $limit
    ): array {
        // -------------------------------------
        // 1. Sous-requête : versions maximales par DA
        // -------------------------------------
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select(
            'd.numeroDemandeApproMere',
            'd.numeroDemandeAppro',
            'MAX(d.numeroVersion) as maxVersion'
        )
            ->from(DaAfficher::class, 'd')
            ->andWhere('d.deleted = 0')
            ->groupBy('d.numeroDemandeApproMere, d.numeroDemandeAppro');

        // Appliquer les filtres sur la sous-requête
        $this->applyDynamicFilters($subQb, "d", $criteria);
        $this->applyAgencyServiceFilters($subQb, "d", $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($subQb, "d", $criteria);
        $this->applyFilterAppro($subQb, "d", $estAppro, $estAdmin);
        $this->applyStatutsFilters($subQb, "d", $criteria);

        // ---------------------------------
        // 2. Compter distinctement les DA mères
        // ---------------------------------
        $countQb = clone $subQb;
        $countQb->resetDQLPart('select');
        $countQb->resetDQLPart('orderBy');
        $countQb->resetDQLPart('groupBy');
        $countQb->select('COUNT(DISTINCT d.numeroDemandeApproMere)');

        $totalItems = (int) $countQb->getQuery()
            ->getSingleScalarResult();

        $lastPage = (int) ceil($totalItems / $limit);

        // ---------------------------
        // 3. Paginer par DA mère
        // ---------------------------
        // D'abord récupérer les DA mères paginées
        $paginatedMeresQb = clone $subQb;
        $paginatedMeresQb->resetDQLPart('select');
        $paginatedMeresQb->resetDQLPart('groupBy');

        // ✅ Sélectionner les colonnes nécessaires pour le ORDER BY (pour SQL Server)
        $paginatedMeresQb->select('d.numeroDemandeApproMere')
            ->addSelect('MAX(d.dateDemande) as maxDateDemande')
            ->groupBy('d.numeroDemandeApproMere');

        $this->handleOrderBy($paginatedMeresQb, 'd', $criteria, true);
        $paginatedMeresQb
            ->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginatedMeres = array_column(
            $paginatedMeresQb->getQuery()->getArrayResult(),
            'numeroDemandeApproMere'
        );

        if (empty($paginatedMeres)) {
            return [
                'results'     => [],
                'totalItems'  => 0,
                'currentPage' => $page,
                'lastPage'    => 0,
            ];
        }

        // Maintenant récupérer toutes les versions max pour ces DA mères
        $versionsQb = clone $subQb;
        $versionsQb->andWhere('d.numeroDemandeApproMere IN (:paginatedMeres)')
            ->setParameter('paginatedMeres', $paginatedMeres);

        $this->handleOrderBy($versionsQb, 'd', $criteria, true);
        $versionsQb
            ->addOrderBy('MAX(d.numeroDemandeApproMere)', 'DESC')
            ->addOrderBy('MAX(d.numeroDemandeAppro)', 'DESC');

        $latestVersions = $versionsQb->getQuery()
            ->getArrayResult();

        // ------------------------------------
        // 4. Construire la requête principale
        // ------------------------------------
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d')
            ->from(DaAfficher::class, 'd')
            ->andWhere('d.deleted = 0');

        // Condition sur les DA mères paginées
        $qb->andWhere('d.numeroDemandeApproMere IN (:paginatedMeres)')
            ->setParameter('paginatedMeres', $paginatedMeres);

        // Condition sur les versions maximales (à partir de la sous-requête)
        $orX = $qb->expr()->orX();
        foreach ($latestVersions as $i => $version) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('d.numeroDemandeAppro', ':numDa' . $i),
                    $qb->expr()->eq('d.numeroVersion', ':maxVer' . $i)
                )
            );
            $qb->setParameter('numDa' . $i, $version['numeroDemandeAppro']);
            $qb->setParameter('maxVer' . $i, $version['maxVersion']);
        }
        $qb->andWhere($orX);

        // Appliquer les mêmes filtres sur la requête principale
        $this->applyDynamicFilters($qb, "d", $criteria);
        $this->applyAgencyServiceFilters($qb, "d", $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($qb, "d", $criteria);
        $this->applyFilterAppro($qb, "d", $estAppro, $estAdmin);
        $this->applyStatutsFilters($qb, "d", $criteria);

        // Ordre final
        $this->handleOrderBy($qb, 'd', $criteria);
        $qb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC')
            ->addOrderBy('d.numeroFournisseur', 'DESC')
            ->addOrderBy('d.numeroCde', 'DESC');

        // ----------------------
        // 5. Retour
        // ----------------------
        return [
            'data'        => $qb->getQuery()->getResult(),
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    private function handleOrderBy(QueryBuilder $qb, string $qbLabel, $criteria, $aggregation = false)
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

        // Fallback par défaut ou ordre secondaire
        $dateDemandeExpr = $aggregation ? "MAX($qbLabel.dateDemande)" : "$qbLabel.dateDemande";
        $qb->addOrderBy($dateDemandeExpr, 'DESC');
    }

    private function applyFilterAppro(QueryBuilder $qb, string $qbLabel, bool $estAppro, bool $estAdmin): void
    {
        if (!$estAdmin && $estAppro) {
            $qb->andWhere($qbLabel . '.statutDal IN (:authorizedStatuts)')
                ->setParameter('authorizedStatuts', [
                    DemandeAppro::STATUT_SOUMIS_APPRO,
                    DemandeAppro::STATUT_SOUMIS_ATE,
                    DemandeAppro::STATUT_DEMANDE_DEVIS,
                    DemandeAppro::STATUT_DEVIS_A_RELANCER,
                    DemandeAppro::STATUT_EN_COURS_PROPOSITION,
                    DemandeAppro::STATUT_AUTORISER_EMETTEUR,
                    DemandeAppro::STATUT_VALIDE,
                    DemandeAppro::STATUT_REFUSE_APPRO,
                    DemandeAppro::STATUT_TERMINER
                ], ArrayParameterType::STRING);
        }
    }

    private function applyDynamicFilters(QueryBuilder $qb, string $qbLabel, array $criteria, bool $estCdeFrn = false): void
    {
        if ($estCdeFrn) {
            $map = [
                'numDa'         => "$qbLabel.numeroDemandeApproMere",
                'numDit'        => "$qbLabel.numeroDemandeDit",
                'numCde'        => "$qbLabel.numeroCde",
                'numOr'         => "$qbLabel.numeroOr",
                'numFrn'        => "$qbLabel.numeroFournisseur",
                'frn'           => "$qbLabel.nomFournisseur",
                'niveauUrgence' => "$qbLabel.niveauUrgence",
            ];
        } else {
            $map = [
                'numDa'         => "$qbLabel.numeroDemandeApproMere",
                'numDit'        => "$qbLabel.numeroDemandeDit",
                'demandeur'     => "$qbLabel.demandeur",
                'codeCentrale'  => "$qbLabel.codeCentrale",
                'niveauUrgence' => "$qbLabel.niveauUrgence",
            ];
        }


        foreach ($map as $key => $field) {
            if (!empty($criteria[$key])) {
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
        }

        if (isset($criteria['typeAchat'])) {
            $qb->andWhere("$qbLabel.daTypeId = :typeAchat")
                ->setParameter('typeAchat', $criteria['typeAchat']);
        }


        if (empty($criteria['numDit']) && empty($criteria['numDa'])) {
            $qb->leftJoin("$qbLabel.dit", 'dit')
                ->leftJoin('dit.idStatutDemande', 'statut')
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

    private function applyStatutsFilters(QueryBuilder $queryBuilder, string $qbLabel, array $criteria, bool $estCdeFrn = false)
    {
        if ($estCdeFrn) {
            if (!empty($criteria['statutBC'])) {
                $queryBuilder->andWhere($qbLabel . '.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBC']);
            }

            if (!empty($criteria['statutDA'])) {
                $queryBuilder->andWhere($qbLabel . '.statutDal = :statutDa')
                    ->setParameter('statutDa', $criteria['statutDA']);
            } elseif (empty($criteria['numDa'])) {
                $queryBuilder->andWhere($qbLabel . '.statutDal NOT IN (:statutDa)')
                    ->setParameter('statutDa', [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_CLOTUREE], ArrayParameterType::STRING);
            }
        } else {
            if (!empty($criteria['statutDA'])) {
                $queryBuilder->andWhere($qbLabel . '.statutDal = :statutDa')
                    ->setParameter('statutDa', $criteria['statutDA']);
            } elseif (empty($criteria['numDa'])) {
                $queryBuilder->andWhere($qbLabel . '.statutDal NOT IN (:statutDa)')
                    ->setParameter('statutDa', [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_CLOTUREE], ArrayParameterType::STRING);
            }

            if (!empty($criteria['statutOR'])) {
                $queryBuilder->andWhere($qbLabel . '.statutOr = :statutOr')
                    ->setParameter('statutOr', $criteria['statutOR']);
            }

            if (!empty($criteria['statutBC'])) {
                $queryBuilder->andWhere($qbLabel . '.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBC']);
            }
        }
    }


    private function applyDateFilters($qb, string $qbLabel, array $criteria, bool $estCdeFrn = false)
    {
        if ($estCdeFrn) {
            /** Date fin souhaite */
            if (!empty($criteria['dateDebutfinSouhaite']) && $criteria['dateDebutfinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }

            if (!empty($criteria['dateFinFinSouhaite']) && $criteria['dateFinFinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }

            /** DATE PLANNING OR */
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }

            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere($qbLabel . '.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }
        } else {
            /** Date fin souhaite */
            if (!empty($criteria['dateDebutfinSouhaite']) && $criteria['dateDebutfinSouhaite']) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }

            if (!empty($criteria['dateFinFinSouhaite']) && $criteria['dateFinFinSouhaite']) {
                $qb->andWhere($qbLabel . '.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }

            /** Date DA (date de demande) */
            if (!empty($criteria['dateDebutCreation']) && $criteria['dateDebutCreation']) {
                $qb->andWhere($qbLabel . '.dateDemande >= :dateDemandeDebut')
                    ->setParameter('dateDemandeDebut', $criteria['dateDebutCreation']);
            }

            if (!empty($criteria['dateFinCreation']) && $criteria['dateFinCreation']) {
                $qb->andWhere($qbLabel . '.dateDemande <= :dateDemandeFin')
                    ->setParameter('dateDemandeFin', $criteria['dateFinCreation']);
            }

            /** DATE PLANNING OR */
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

    private function applyAgencyServiceFilters($qb, string $qbLabel, array $criteria, User $user, int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin)
    {
        if (!$estAtelier && !$estAppro && !$estAdmin) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        "$qbLabel.agenceDebiteur IN (:agenceAutoriserIds)",
                        "$qbLabel.agenceEmetteur = :codeAgence"
                    )
                )
                ->setParameter('agenceAutoriserIds', $user->getAgenceAutoriserIds(), ArrayParameterType::INTEGER)
                ->setParameter('codeAgence', $idAgenceUser)
                ->andWhere(
                    $qb->expr()->orX(
                        "$qbLabel.serviceDebiteur IN (:serviceAutoriserIds)",
                        "$qbLabel.serviceEmetteur IN (:serviceAutoriserIds)"
                    )
                )
                ->setParameter('serviceAutoriserIds', $user->getServiceAutoriserIds(), ArrayParameterType::INTEGER);
        }

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
    }

    public function getNbrDaAfficherValider(string $numeroOr): int
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroOr = :numOr')
            ->setParameter('numOr', $numeroOr)
            ->getQuery()
            ->getSingleScalarResult();
        if ($numeroVersionMax === null) {
            return 0;
        }
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id) AS nombreDaAfficherValider')
            ->where('d.numeroOr = :numOr')
            ->andWhere('d.statutDal = :statutValide')
            ->andWhere('d.numeroVersion = :numVersion')
            ->setParameters([
                'numOr' => $numeroOr,
                'statutValide' => DemandeAppro::STATUT_VALIDE,
                'numVersion' => $numeroVersionMax
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * recupère le derière statut du DA afficher
     * @param string $numeroDemandeAppro
     */
    public function getLastStatutDaAfficher(string $numeroDemandeAppro)
    {
        //recupérer dabor le numéro de version max
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numeroDemandeAppro')
            ->setParameter('numeroDemandeAppro', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        //recupérer le derière statut du DA afficher
        return $this->createQueryBuilder('d')
            ->select('d.statutDal')
            ->where('d.numeroDemandeAppro = :numeroDemandeAppro')
            ->andWhere('d.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numeroDemandeAppro' => $numeroDemandeAppro,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->getQuery()
            ->getSingleColumnResult();
    }


    public function findDerniereVersionDesDA(User $user, array $criteria,  int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin): array //liste_da
    {
        $qb = $this->createQueryBuilder('d');

        $qb->where(
            'd.numeroVersion = (
                    SELECT MAX(d2.numeroVersion)
                    FROM ' . DaAfficher::class . ' d2
                    WHERE d2.numeroDemandeAppro = d.numeroDemandeAppro
                )'
        )
            ->andWhere('d.deleted = :deleted')
            ->setParameter('deleted', 0);

        $this->applyDynamicFilters($qb, 'd', $criteria);
        $this->applyAgencyServiceFilters($qb, 'd', $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($qb, 'd', $criteria);

        $this->applyFilterAppro($qb, 'd', $estAppro, $estAdmin);
        $this->applyStatutsFilters($qb, 'd', $criteria);

        $qb->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroFournisseur', 'DESC')
            ->addOrderBy('d.numeroCde', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function getStatutsBc()
    {
        $originalArray =  $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutCde')
            ->where('d.statutCde IS NOT NULL')
            ->andWhere('d.statutCde != :statutVide')
            ->setParameter('statutVide', '')
            ->orderBy('d.statutCde', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return array_combine($originalArray, $originalArray);
    }

    public function getTimelineData(string $numDa)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutDal', 'd.statutOr', 'd.dateCreation', 'd.dateDemande')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->orderBy('d.dateCreation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getTimelineDataForBC(string $numDa)
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$numeroVersionMax) return [];

        $qb = $this->createQueryBuilder('d')
            ->select('DISTINCT d.numeroCde', 'd.dateCreationBc', 'd.dateValidationBc', 'd.dateEnvoiFournisseur')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersionMax')
            ->andWhere('d.numeroCde IS NOT NULL')
            ->andWhere('d.numeroCde != :vide')
            ->setParameters([
                'vide' => '',
                'numDa' => $numDa,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->orderBy('d.dateCreationBc', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
