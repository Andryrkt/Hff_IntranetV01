<?php

namespace App\Repository\da;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DaAfficher;
use App\Entity\dit\DemandeIntervention;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
    public function getDateLivraisonPrevue(string $numeroDemandeAppro, string $numeroCde, string $codeSociete)
    {
        $maxVersion = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :num')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('num', $numeroDemandeAppro)
            ->setParameter('codeSociete', $codeSociete)
            ->getQuery()
            ->getSingleScalarResult(); // Renvoie null si aucune ligne

        if ($maxVersion === null) {
            return null;
        } else {
            $result = $this->createQueryBuilder('d')
                ->select('DISTINCT(d.dateLivraisonPrevue) as dateLivraisonPrevue')
                ->where('d.numeroDemandeAppro = :num')
                ->andWhere('d.numeroCde = :numCde')
                ->andWhere('d.codeSociete = :codeSociete')
                ->andWhere('d.numeroVersion = :version')
                ->andWhere('d.dateLivraisonPrevue IS NOT NULL')
                ->setParameters([
                    'num'         => $numeroDemandeAppro,
                    'numCde'      => $numeroCde,
                    'codeSociete' => $codeSociete,
                    'version'     => $maxVersion,
                ])
                ->getQuery()
                ->getOneOrNullResult();

            return $result ? $result['dateLivraisonPrevue'] : null;
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
    public function getNumeroVersionMax(string $numeroDemandeAppro, string $codeSociete)
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroDemandeAppro = :numDa')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->setParameter('codeSociete', $codeSociete)
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
     * @param string $codeSociete
     * 
     * @return int
     */
    public function getNumeroVersionMaxCde(string $numeroCde, string $codeSociete): int
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('DISTINCT MAX(d.numeroVersion)')
            ->where('d.numeroCde = :numCde')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('numCde', $numeroCde)
            ->setParameter('codeSociete', $codeSociete)
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
     * Récupère les dernières versions de DA pour la liste cde frn
     * Regroupé par DA mère pour la pagination
     * @param array $criteria
     * @param int $page
     * @param int $limit
     * @param string $codeSociete
     * 
     * @return array
     */
    public function findValidatedPaginatedDas(?array $criteria = [], int $page, int $limit, string $codeSociete): array
    {
        $criteria = $criteria ?? [];

        // ------------------------------------------------------------------
        // Constantes métier
        // ------------------------------------------------------------------
        $statutOrs = [
            StatutOrConstant::STATUT_VALIDE,
            StatutDaConstant::STATUT_DW_VALIDEE,
        ];

        $statutDas = [
            StatutDaConstant::STATUT_CLOTUREE,
            StatutDaConstant::STATUT_VALIDE,
        ];

        $exceptions = ['DAP25079981'];

        // ------------------------------------------------------------------
        // Sous-requête DQL simplifiée : MAX(version)
        // ------------------------------------------------------------------
        $subDql = '
        SELECT MAX(sub.numeroVersion)
        FROM ' . DaAfficher::class . ' sub
        WHERE sub.numeroDemandeAppro = d.numeroDemandeAppro
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
            ->andWhere('d.codeSociete = :codeSociete')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->in('d.statutOr', ':statutOrs'),
                $qb->expr()->in('d.numeroDemandeAppro', ':exceptions')
            ))
            ->setParameter('statutPasDansOr', StatutBcConstant::STATUT_PAS_DANS_OR)
            ->setParameter('codeSociete', $codeSociete)
            ->setParameter('statutDal', $statutDas)
            ->setParameter('statutOrs', $statutOrs)
            ->setParameter('exceptions', $exceptions);

        // Filtres dynamiques via le service
        $filterService = $this->getFilterService();
        $filterService->applyDynamicFilters($qb, 'd', $criteria, true);
        $filterService->applyStatutsFilters($qb, 'd', $criteria, true);
        $filterService->applyDateFilters($qb, 'd', $criteria, true);
        $filterService->applyAgencyServiceFilters($qb, 'd', $criteria);

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
    public function findValidatedDas(array $criteria = [], string $codeSociete): array
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
            StatutOrConstant::STATUT_VALIDE,
            StatutDaConstant::STATUT_DW_VALIDEE
        ];

        $exceptions = [
            'DAP25079981'
        ];

        $statutDas = [
            StatutDaConstant::STATUT_CLOTUREE,
            StatutDaConstant::STATUT_VALIDE
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
            ->setParameter('statutPasDansOr', StatutBcConstant::STATUT_PAS_DANS_OR);

        // filtres dynamiques via le service
        $filterService = $this->getFilterService();
        $filterService->applyDynamicFilters($qb, "d", $criteria, true);
        $filterService->applyStatutsFilters($qb, "d", $criteria, true);
        $filterService->applyDateFilters($qb, "d", $criteria, true);

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
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete)
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
        string $codeSociete
    ): array {
        $criteria = $criteria ?? [];

        // 1. Sous-requête DQL pour la version max corrélée
        $subDql = 'SELECT MAX(sub.numeroVersion) FROM ' . DaAfficher::class . ' sub WHERE sub.numeroDemandeAppro = d.numeroDemandeAppro';

        // 2. Requête de base
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d', 'da', 'dap', 'dit')
            ->from(DaAfficher::class, 'd')
            ->leftJoin('d.demandeAppro', 'da')
            ->leftJoin('d.demandeApproParent', 'dap')
            ->leftJoin('d.dit', 'dit')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete)
            ->andWhere('d.numeroVersion = (' . $subDql . ')');

        // 3. Appliquer les filtres métier via le service
        $filterService = $this->getFilterService();
        $filterService->applyDynamicFilters($qb, "d", $criteria);
        $filterService->applyAgencyServiceFilters($qb, "d", $criteria);
        $filterService->applyDateFilters($qb, "d", $criteria);
        $filterService->applyStatutsFilters($qb, "d", $criteria);

        // $query = $qb->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }

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

        $this->getFilterService()->handleOrderBy($motherQb, 'd', $criteria, true);
        $motherQb->addOrderBy('d.numeroDemandeApproMere', 'DESC');
        $motherQb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $motherIds = array_column($motherQb->getQuery()->getArrayResult(), 'numeroDemandeApproMere');

        // 6. Fetch final de toutes les lignes pour ces mères
        $finalQb = $this->_em->createQueryBuilder();
        $finalQb->select('d', 'da', 'dap', 'dit')
            ->from(DaAfficher::class, 'd')
            ->leftJoin('d.demandeAppro', 'da')
            ->leftJoin('d.demandeApproParent', 'dap')
            ->leftJoin('d.dit', 'dit')
            ->andWhere('d.deleted = 0')
            ->andWhere('d.numeroVersion = (' . $subDql . ')')
            ->andWhere('d.numeroDemandeApproMere IN (:motherIds)')
            ->setParameter('motherIds', $motherIds);

        $this->getFilterService()->handleOrderBy($finalQb, 'd', $criteria);
        $finalQb->addOrderBy('d.numeroDemandeApproMere', 'DESC')
            ->addOrderBy('d.numeroDemandeAppro', 'DESC')
            ->addOrderBy('d.numeroCde', 'ASC')
        ;

        return [
            'data'        => $finalQb->getQuery()->getResult(),
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    /**
     * Méthode privée pour récupérer le service de filtrage via le conteneur global
     */
    private function getFilterService(): \App\Service\da\DaFilterService
    {
        global $container;
        return $container->get(\App\Service\da\DaFilterService::class);
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
                'statutValide' => StatutDaConstant::STATUT_VALIDE,
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


    public function findDerniereVersionDesDA(
        array $criteria,
        string $codeSociete
    ): array {
        $qb = $this->createQueryBuilder('d');

        $qb->where(
            'd.numeroVersion = (
                    SELECT MAX(d2.numeroVersion)
                    FROM ' . DaAfficher::class . ' d2
                    WHERE d2.numeroDemandeAppro = d.numeroDemandeAppro
                )'
        )
            ->andWhere('d.deleted = :deleted')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete)
            ->setParameter('deleted', 0);

        $filterService = $this->getFilterService();
        $filterService->applyDynamicFilters($qb, 'd', $criteria);
        $filterService->applyAgencyServiceFilters($qb, 'd', $criteria);
        $filterService->applyDateFilters($qb, 'd', $criteria);
        $filterService->applyStatutsFilters($qb, 'd', $criteria);

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

    public function getInfoDa(int $numCde)
    {
        return  $this->createQueryBuilder('da')
            ->select('da.agenceDebiteur, da.serviceDebiteur, da.numeroOr, da.numeroFournisseur, da.numeroDemandeAppro, da.daTypeId')
            ->where('da.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getNumFrnDa(int $numcde)
    {
        return $this->createQueryBuilder('da')
            ->select('da.numeroFournisseur')
            ->where('da.numeroCde = :numCde')
            ->setParameter('numCde', $numcde)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getTypeDa($numCde)
    {
        return $this->createQueryBuilder('da')
            ->select('da.daTypeId')
            ->where('da.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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

    public function getTypeDaSelonNumDa(string $numDa)
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.daTypeId as daTypeId')
            ->where('d.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['daTypeId'] : null;
    }
}
