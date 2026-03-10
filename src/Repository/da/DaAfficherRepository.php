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
use Doctrine\ORM\Query;

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

        // ------------------------------------------------------------------
        // Constantes métier
        // ------------------------------------------------------------------
        $statutOrs = [
            DitOrsSoumisAValidation::STATUT_VALIDE,
            DemandeAppro::STATUT_DW_VALIDEE,
        ];

        $statutDas = [
            DemandeAppro::STATUT_CLOTUREE,
            DemandeAppro::STATUT_VALIDE,
        ];

        $exceptions = ['DAP25079981'];

        // ------------------------------------------------------------------
        // Sous-requête DQL simplifiée : MAX(version)
        // ------------------------------------------------------------------
        $subDql = '
        SELECT MAX(sub.numeroVersion)
        FROM ' . DaAfficher::class . ' sub
        WHERE sub.numeroDemandeAppro = d.numeroDemandeAppro
          AND sub.statutDal IN (:statutDal)
          AND (sub.statutOr IN (:statutOrs) OR sub.numeroDemandeAppro IN (:exceptions))
    ';

        // ------------------------------------------------------------------
        // Requête principale
        // ------------------------------------------------------------------
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d', 'da', 'dit')
            ->from(DaAfficher::class, 'd')
            ->leftJoin('d.demandeAppro', 'da')
            ->leftJoin('d.dit', 'dit')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.statutCde IS NULL OR d.statutCde != :statutPasDansOr')
            ->andWhere('d.numeroVersion = (' . $subDql . ')')
            ->andWhere('d.statutDal IN (:statutDal)')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->in('d.statutOr', ':statutOrs'),
                $qb->expr()->in('d.numeroDemandeAppro', ':exceptions')
            ))
            ->setParameter('statutPasDansOr', DaSoumissionBc::STATUT_PAS_DANS_OR)
            ->setParameter('statutDal', $statutDas)
            ->setParameter('statutOrs', $statutOrs)
            ->setParameter('exceptions', $exceptions);

        // Filtres dynamiques
        $this->applyDynamicFilters($qb, 'd', $criteria, true);
        $this->applyStatutsFilters($qb, 'd', $criteria, true);
        $this->applyDateFilters($qb, 'd', $criteria, true);

        // ------------------------------------------------------------------
        // COUNT optimisé (COUNT(d.id) est plus rapide que DISTINCT)
        // ------------------------------------------------------------------
        $countQb = clone $qb;
        $countQb->resetDQLPart('select');
        $countQb->resetDQLPart('orderBy');
        $countQb->select('COUNT(d.id)');

        // On utilise un cache de 5 minutes pour le total afin d'accélérer la navigation
        $totalItems = (int) $countQb->getQuery()
            ->useResultCache(true, 300, 'da_cde_frn_count_' . md5(serialize($criteria)))
            ->getSingleScalarResult();

        if ($totalItems === 0) {
            return [
                'data'        => [],
                'totalItems'  => 0,
                'currentPage' => $page,
                'lastPage'    => 0,
            ];
        }

        $lastPage = (int) ceil($totalItems / $limit);

        // ------------------------------------------------------------------
        // Tri
        // ------------------------------------------------------------------
        if (!empty($criteria['sortNbJours'])) {
            $qb->orderBy('d.joursDispo', $criteria['sortNbJours']);
        } else {
            $qb->orderBy('d.dateDemande', 'DESC')
                ->addOrderBy('d.numeroFournisseur', 'DESC')
                ->addOrderBy('d.numeroCde', 'DESC');
        }

        $qb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC');

        // ------------------------------------------------------------------
        // Pagination
        // ------------------------------------------------------------------
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // ------------------------------------------------------------------
        // Résultat
        // ------------------------------------------------------------------
        return [
            'data'        => $qb->getQuery()->getResult(),
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    /**
     * pour le expor excel liste cde frn
     *
     * @param array $criteria
     * @return array
     */
    public function findValidatedDas(array $criteria = []): array
    {
        // -------------------------------------
        // 1. Sous-requête : versions maximales par DA
        // -------------------------------------
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select(
            'd.numeroDemandeAppro',
            'MAX(d.numeroVersion) as maxVersion'
        )
            ->from(DaAfficher::class, 'd')
            ->groupBy('d.numeroDemandeAppro');

        $statutOrs = [
            DitOrsSoumisAValidation::STATUT_VALIDE,
            DemandeAppro::STATUT_DW_VALIDEE
        ];

        $exceptions = [
            'DAP25079981'
        ];

        $statutDas = [
            DemandeAppro::STATUT_CLOTUREE,
            DemandeAppro::STATUT_VALIDE
        ];

        $subQb->andWhere(
            $subQb->expr()->orX(
                $subQb->expr()->in('d.statutOr', ':statutOrs'),
                $subQb->expr()->in('d.numeroDemandeAppro', ':exceptions')
            )
        );

        $subQb->andWhere('d.statutDal IN (:statutDal)');

        $subQb->setParameter('statutOrs', $statutOrs)
            ->setParameter('exceptions', $exceptions)
            ->setParameter('statutDal', $statutDas);

        $latestVersions = $subQb->getQuery()->getArrayResult();

        if (empty($latestVersions)) {
            return [];
        }

        // Mapping numéro DA -> version max
        $latestVersionsMap = [];
        foreach ($latestVersions as $version) {
            $latestVersionsMap[$version['numeroDemandeAppro']] = $version['maxVersion'];
        }

        // -------------------------------------
        // 2. Requête principale
        // -------------------------------------
        $qb = $this->_em->createQueryBuilder();

        $qb->select('d')
            ->from(DaAfficher::class, 'd')
            ->where($qb->expr()->orX(
                'd.statutCde != :statutPasDansOr',
                'd.statutCde IS NULL'
            ))
            ->andWhere('d.deleted = 0')
            ->setParameter('statutPasDansOr', DaSoumissionBc::STATUT_PAS_DANS_OR);

        // filtres dynamiques
        $this->applyDynamicFilters($qb, "d", $criteria, true);
        $this->applyStatutsFilters($qb, "d", $criteria, true);
        $this->applyDateFilters($qb, "d", $criteria, true);

        // garder uniquement les dernières versions
        $orX = $qb->expr()->orX();
        $paramIndex = 0;

        foreach ($latestVersionsMap as $numeroDemandeAppro => $maxVersion) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('d.numeroDemandeAppro', ':numDa' . $paramIndex),
                    $qb->expr()->eq('d.numeroVersion', ':maxVer' . $paramIndex)
                )
            );

            $qb->setParameter('numDa' . $paramIndex, $numeroDemandeAppro);
            $qb->setParameter('maxVer' . $paramIndex, $maxVersion);

            $paramIndex++;
        }

        $qb->andWhere($orX);

        // statuts
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

        // tri
        if (!empty($criteria['sortNbJours'])) {
            $qb->orderBy('d.joursDispo', $criteria['sortNbJours']);
        } else {
            $qb->orderBy('d.dateDemande', 'DESC')
                ->addOrderBy('d.numeroFournisseur', 'DESC')
                ->addOrderBy('d.numeroCde', 'DESC');
        }

        $qb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction publique : renvoie les DA paginés avec filtres appliqués uniquement sur les dernières versions
     * OPTIMISÉE : Utilise une sous-requête corrélée au lieu d'une boucle PHP massive.
     */
    public function findPaginatedAndFilteredDA(
        int $page,
        int $limit,
        array $criteria,
        int $agenceIdUser,
        int $serviceIdUser,
        array $agenceServiceAutorises,
        bool $peutVoirListeAvecDebiteur
    ): array {
        $criteria = $criteria ?? [];

        // 1. Sous-requête DQL pour la version max corrélée
        $subDql = 'SELECT MAX(sub.numeroVersion) FROM ' . DaAfficher::class . ' sub WHERE sub.numeroDemandeAppro = d.numeroDemandeAppro';

        // 2. Requête de base
        $qb = $this->_em->createQueryBuilder();
        $qb->select(
            'd.id',
            'd.objetDal',
            'd.numeroDemandeAppro',
            'd.numeroDemandeApproMere',
            'd.numeroDemandeDit',
            'd.niveauUrgence',
            'd.numeroOr',
            'd.datePlannigOr',
            'd.numeroFournisseur',
            'd.nomFournisseur',
            'd.numeroCde',
            'd.statutDal',
            'd.statutCde',
            'd.statutOr',
            'd.statutOr',
            'd.dateFinSouhaite',
            'd.artConstp',
            'd.artRefp',
            'd.artDesi',
            'd.qteDem',
            'd.qteEnAttent',
            'd.qteDispo',
            'd.qteLivrer',
            'd.dateLivraisonPrevue',
            'd.joursDispo',
            'd.demandeur',
            'd.daTypeId',
            'd.numeroLigne',
            'd.positionBc',
            'd.dateDemande',
            'd.estDalr',
            'd.estFicheTechnique',
            'd.desiCentrale',
            'da.id   AS demandeApproId',
            'dap.id  AS demandeApproParentId',
            'dit.id  AS ditId'
        )
            ->from(DaAfficher::class, 'd')
            ->leftJoin('d.demandeAppro', 'da')
            ->leftJoin('d.demandeApproParent', 'dap')
            ->leftJoin('d.dit', 'dit')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.numeroVersion = (' . $subDql . ')');

        // 3. Appliquer les filtres métier
        $this->applyDynamicFilters($qb, 'd', $criteria);
        $this->applyAgencyServiceFilters($qb, 'd', $criteria);
        $this->applyDateFilters($qb, 'd', $criteria);
        $this->applyStatutsFilters($qb, 'd', $criteria);
        $this->conditionAgenceService($qb, 'd', $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);
        // 4. Count total optimisé avec cache
        $countQb = clone $qb;
        $countQb->resetDQLPart('select');
        $countQb->resetDQLPart('orderBy');
        $countQb->select('COUNT(DISTINCT d.numeroDemandeApproMere)');

        $totalItems = (int) $countQb->getQuery()
            ->useResultCache(true, 300, 'da_list_count_' . md5(serialize($criteria) . $agenceIdUser . $serviceIdUser))
            ->getSingleScalarResult();

        if ($totalItems === 0) {
            return ['data' => [], 'totalItems' => 0, 'currentPage' => $page, 'lastPage' => 0];
        }

        $lastPage = (int) ceil($totalItems / $limit);

        // 5. Récupérer les DA mères pour la page courante (Pagination par DA mère)
        $motherQb = clone $qb;
        $motherQb->resetDQLPart('select');
        $motherQb->resetDQLPart('orderBy');
        $motherQb->select('d.numeroDemandeApproMere');
        $motherQb->groupBy('d.numeroDemandeApproMere'); // Utilisation de GROUP BY pour SQL Server

        $this->handleOrderBy($motherQb, 'd', $criteria, true);
        $motherQb->addOrderBy('d.numeroDemandeApproMere', 'DESC');
        $motherQb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $motherIds = array_column($motherQb->getQuery()->getArrayResult(), 'numeroDemandeApproMere');

        // 6. Fetch final de toutes les lignes pour ces mères
        $finalQb = $this->_em->createQueryBuilder();
        $finalQb->select(
            'd.id',
            'd.objetDal',
            'd.numeroDemandeAppro',
            'd.numeroDemandeApproMere',
            'd.numeroDemandeDit',
            'd.niveauUrgence',
            'd.numeroOr',
            'd.datePlannigOr',
            'd.numeroFournisseur',
            'd.nomFournisseur',
            'd.numeroCde',
            'd.statutDal',
            'd.statutCde',
            'd.statutOr',
            'd.dateFinSouhaite',
            'd.artConstp',
            'd.artRefp',
            'd.artDesi',
            'd.qteDem',
            'd.qteEnAttent',
            'd.qteDispo',
            'd.qteLivrer',
            'd.dateLivraisonPrevue',
            'd.joursDispo',
            'd.demandeur',
            'd.daTypeId',
            'd.numeroLigne',
            'd.positionBc',
            'd.dateDemande',
            'd.estDalr',
            'd.estFicheTechnique',
            'd.desiCentrale',
            'da.id   AS demandeApproId',
            'dap.id  AS demandeApproParentId',
            'dit.id  AS ditId'
        )
            ->from(DaAfficher::class, 'd')
            ->leftJoin('d.demandeAppro', 'da')
            ->leftJoin('d.demandeApproParent', 'dap')
            ->leftJoin('d.dit', 'dit')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.numeroVersion = (' . $subDql . ')')
            ->andWhere('d.numeroDemandeApproMere IN (:motherIds)')
            ->setParameter('motherIds', $motherIds);

        $this->handleOrderBy($finalQb, 'd', $criteria);
        $finalQb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC');

        return [
            'data'        => $finalQb->getQuery()->getResult(Query::HYDRATE_ARRAY),
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
                $criteria = $key === 'numDa' ? $this->supprimerQuatriemeLettrePD3($criteria[$key]) : $criteria[$key];
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $criteria);
            }
        }

        if (isset($criteria['typeAchat'])) {
            $qb->andWhere("$qbLabel.daTypeId = :typeAchat")
                ->setParameter('typeAchat', $criteria['typeAchat']);
        }


        if (empty($criteria['numDit']) && empty($criteria['numDa'])) {
            // Vérifier si la jointure sur 'dit' existe déjà pour éviter l'erreur "already defined"
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

    private function applyAgencyServiceFilters($qb, string $qbLabel, array $criteria)
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
    }

    private function conditionAgenceService($queryBuilder, string $queryLabel, int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur)
    {
        $ORX = $queryBuilder->expr()->orX();

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

    public function getNbrDaAfficherValider(string $numeroOr, string $codeSociete): int
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroOr = :numOr')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('numOr', $numeroOr)
            ->setParameter('codeSociete', $codeSociete)
            ->getQuery()
            ->getSingleScalarResult();
        if ($numeroVersionMax === null) {
            return 0;
        }
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id) AS nombreDaAfficherValider')
            ->where('d.numeroOr = :numOr')
            ->andWhere('d.statutDal = :statutValide')
            ->andWhere('d.codeSociete = :codeSociete')
            ->andWhere('d.numeroVersion = :numVersion')
            ->setParameters([
                'codeSociete' => $codeSociete,
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
    public function getLastStatutDaAfficher(string $numeroDemandeAppro, string $codeSociete)
    {
        //recupérer dabor le numéro de version max
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numeroDemandeAppro')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('numeroDemandeAppro', $numeroDemandeAppro)
            ->setParameter('codeSociete', $codeSociete)
            ->getQuery()
            ->getSingleScalarResult();

        //recupérer le derière statut du DA afficher
        return $this->createQueryBuilder('d')
            ->select('d.statutDal')
            ->where('d.numeroDemandeAppro = :numeroDemandeAppro')
            ->andWhere('d.numeroVersion = :numeroVersionMax')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameters([
                'codeSociete' => $codeSociete,
                'numeroDemandeAppro' => $numeroDemandeAppro,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->getQuery()
            ->getSingleColumnResult();
    }


    public function findDerniereVersionDesDA(array $criteria, int $agenceIdUser, int $serviceIdUser, array $agenceServiceAutorises, bool $peutVoirListeAvecDebiteur): array
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
        $this->applyAgencyServiceFilters($qb, 'd', $criteria);
        $this->applyDateFilters($qb, 'd', $criteria);
        // $this->applyFilterAppro($qb, 'd', $estAppro, $estAdmin);
        $this->applyStatutsFilters($qb, 'd', $criteria);
        $this->conditionAgenceService($qb, "d", $agenceIdUser, $serviceIdUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur);

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

    public function getAllNumCdeAndVmax(string $numDa)
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$numeroVersionMax) return [];

        $qb = $this->createQueryBuilder('d')
            ->select('DISTINCT d.numeroCde', 'd.numeroVersion')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersionMax')
            ->andWhere('d.numeroCde IS NOT NULL')
            ->andWhere('d.numeroCde != :vide')
            ->setParameters([
                'vide' => '',
                'numDa' => $numDa,
                'numeroVersionMax' => $numeroVersionMax
            ])
            ->orderBy('d.numeroCde', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getDateCreationBc(string $numDa, int $numeroVersion, string $numeroCde): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('MIN(d.dateCreationBc)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->andWhere('d.numeroCde = :numeroCde')
            ->andWhere('d.dateCreationBc IS NOT NULL')
            ->setParameters([
                'numDa' => $numDa,
                'numeroVersion' => $numeroVersion,
                'numeroCde' => $numeroCde
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new \DateTime($result) : null;
    }

    public function getDateValidationBc(string $numDa, int $numeroVersion, string $numeroCde): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('MIN(d.dateValidationBc)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->andWhere('d.numeroCde = :numeroCde')
            ->andWhere('d.dateValidationBc IS NOT NULL')
            ->setParameters([
                'numDa' => $numDa,
                'numeroVersion' => $numeroVersion,
                'numeroCde' => $numeroCde
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new \DateTime($result) : null;
    }

    public function getDateEnvoiFournisseur(string $numDa, int $numeroVersion, string $numeroCde): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('MIN(d.dateEnvoiFournisseur)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->andWhere('d.numeroCde = :numeroCde')
            ->andWhere('d.dateEnvoiFournisseur IS NOT NULL')
            ->setParameters([
                'numDa' => $numDa,
                'numeroVersion' => $numeroVersion,
                'numeroCde' => $numeroCde
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new \DateTime($result) : null;
    }

    public function getDateReceptionArticle(string $numDa, int $numeroVersion, string $numeroCde): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('MIN(d.dateReceptionArticle)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->andWhere('d.numeroCde = :numeroCde')
            ->andWhere('d.dateReceptionArticle IS NOT NULL')
            ->setParameters([
                'numDa' => $numDa,
                'numeroVersion' => $numeroVersion,
                'numeroCde' => $numeroCde
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new \DateTime($result) : null;
    }

    public function getDateLivraisonArticle(string $numDa, int $numeroVersion, string $numeroCde): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('MIN(d.dateLivraisonArticle)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->andWhere('d.numeroCde = :numeroCde')
            ->andWhere('d.dateLivraisonArticle IS NOT NULL')
            ->setParameters([
                'numDa' => $numDa,
                'numeroVersion' => $numeroVersion,
                'numeroCde' => $numeroCde
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new \DateTime($result) : null;
    }

    public function getTypeDa(string $numDa)
    {
        return $this->createQueryBuilder('d')
            ->select('DISTINCT d.daTypeId')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
