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

    public function markAsDeletedByNumeroLigne(string $numeroDemandeAppro, array $numeroLignes, string $userName, $numeroVersion): void
    {
        if (empty($numeroLignes)) return; // rien à faire

        $this->createQueryBuilder('d')
            ->update()
            ->set('d.deleted', ':deleted')
            ->set('d.deletedBy', ':deletedBy')
            ->where('d.numeroDemandeAppro = :num')
            ->andWhere('d.numeroVersion = :version')
            ->andWhere('d.numeroLigne IN (:lines)')
            ->setParameters([
                'num'       => $numeroDemandeAppro,
                'version'   => $numeroVersion,
                'deleted'   => true,
                'deletedBy' => $userName,
                'lines'     => $numeroLignes,
            ])
            ->getQuery()
            ->execute();
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
     * @param array $criteria
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function findValidatedPaginatedDas(?array $criteria = [], int $page, int $limit): array
    {
        $criteria = $criteria ?? [];

        // ----------------------
        // 1. Sous-requête : versions maximales
        // ----------------------
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select('d.numeroDemandeAppro', 'MAX(d.numeroVersion) as maxVersion')
            ->from(DaAfficher::class, 'd')
            ->groupBy('d.numeroDemandeAppro');

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

        $typeDa = [
            DemandeAppro::TYPE_DA_AVEC_DIT,
            DemandeAppro::TYPE_DA_REAPPRO
        ];
        $typeDaDirect = DemandeAppro::TYPE_DA_DIRECT;
        // Appliquer la condition selon le type de la DA
        $subQb->andWhere(
            $subQb->expr()->orX(
                $subQb->expr()->eq('d.daTypeId', ':typeDaDirect'),
                $subQb->expr()->andX(
                    $subQb->expr()->in('d.daTypeId', ':typeDa'),
                    $orCondition
                )
            )
        )
            ->setParameter('typeDaDirect', $typeDaDirect)
            ->setParameter('typeDa', $typeDa);

        // Paramètres communs
        $subQb->setParameter('statutOrs', $statutOrs)
            ->setParameter('exceptions', $exceptions);

        $this->applyDynamicFilters($subQb, "d", $criteria, true);
        $this->applyStatutsFilters($subQb, "d", $criteria, true);
        $this->applyDateFilters($subQb, "d", $criteria, true);

        // ----------------------
        // 2. Compter distinctement les DA
        // ----------------------
        $countQb = clone $subQb;
        $countQb->resetDQLPart('select');
        $countQb->resetDQLPart('orderBy');
        $countQb->resetDQLPart('groupBy');
        $countQb->select('COUNT(DISTINCT d.numeroDemandeAppro)');

        $totalItems = (int) $countQb->getQuery()
            ->getSingleScalarResult();

        $lastPage = (int) ceil($totalItems / $limit);

        // ----------------------
        // 3. Paginer la sous-requête
        // ----------------------
        $subQb->orderBy('MAX(d.dateDemande)', 'DESC')
            ->addOrderBy('MAX(d.numeroFournisseur)', 'DESC')
            ->addOrderBy('MAX(d.numeroCde)', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $latestVersions = $subQb->getQuery()
            ->getArrayResult();

        if (empty($latestVersions)) {
            return [
                'data'        => [],
                'totalItems'  => 0,
                'currentPage' => $page,
                'lastPage'    => 0,
            ];
        }

        // ----------------------
        // 4. Construire la requête principale
        // ----------------------
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d')
            ->from(DaAfficher::class, 'd')
            ->Where($qb->expr()->orX(
                'd.statutCde != :statutPasDansOr',
                'd.statutCde IS NULL'
            )) // enlever les ligne qui a le statut PAS DANS OR
            ->andWhere('d.deleted = 0')
            ->setParameter('statutPasDansOr', DaSoumissionBc::STATUT_PAS_DANS_OR)
        ;

        $qb->andWhere('d.statutDal = :statutDal')
            ->andWhere($qb->expr()->in('d.statutOr', ':statutOrsValide'))
            ->setParameter('statutOrsValide', $statutOrs)
            ->setParameter('statutDal', DemandeAppro::STATUT_VALIDE);

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
        // triage selon le filtre choisi par l'utilisateur
        if (!empty($criteria['sortNbJours'])) {
            $qb->orderBy('d.joursDispo', $criteria['sortNbJours']);
        } else {
            // Ordre final
            $qb->orderBy('d.dateDemande', 'DESC')
                ->addOrderBy('d.numeroFournisseur', 'DESC')
                ->addOrderBy('d.numeroCde', 'DESC');
        }
        // DEBUT debug
        // $query = $qb->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }
        // FIN debug
        // ----------------------
        // 5. Retour
        // ----------------------
        return [
            'data'        => $qb->getQuery()->getResult(),
            'totalItems'  => $totalItems,   // ✅ Compte correct avec filtres
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    /**
     * Étape 1 : Récupérer les dernières versions de chaque DA
     */
    private function getLastVersions(): array
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d.numeroDemandeAppro, MAX(d.numeroVersion) AS maxVersion')
            ->groupBy('d.numeroDemandeAppro');

        return $qb->getQuery()->getArrayResult(); // retourne [ ['numeroDemandeAppro'=>.., 'maxVersion'=>..], ... ]
    }

    /**
     * Étape 2 : Filtrer les dernières versions et paginer
     */
    private function getFilteredLastVersions(
        User $user,
        array $criteria,
        int $idAgenceUser,
        bool $estAppro,
        bool $estAtelier,
        bool $estAdmin,
        int $page,
        int $limit
    ): array {
        // 1️⃣ Récupérer toutes les dernières versions
        $lastVersions = $this->getLastVersions();
        if (empty($lastVersions)) {
            return [
                'results'    => [],
                'totalItems' => 0
            ];
        }

        $numeroDAs = array_column($lastVersions, 'numeroDemandeAppro');
        $versionsMax = array_column($lastVersions, 'maxVersion', 'numeroDemandeAppro');

        // 2️⃣ Créer la requête sur les dernières versions uniquement
        $qb = $this->createQueryBuilder('daf')
            ->where('daf.numeroDemandeAppro IN (:numeroDAs)')
            ->andWhere('daf.deleted = 0')
            ->setParameter('numeroDAs', $numeroDAs);

        // Limiter aux numéros de version max
        $orX = $qb->expr()->orX();
        foreach ($versionsMax as $numeroDA => $versionMax) {
            $orX->add($qb->expr()->andX(
                $qb->expr()->eq('daf.numeroDemandeAppro', ':da_' . $numeroDA),
                $qb->expr()->eq('daf.numeroVersion', ':ver_' . $numeroDA)
            ));
            $qb->setParameter('da_' . $numeroDA, $numeroDA);
            $qb->setParameter('ver_' . $numeroDA, $versionMax);
        }
        $qb->andWhere($orX);

        // 3️⃣ Appliquer les filtres sur ces dernières versions uniquement
        $this->applyDynamicFilters($qb, "daf", $criteria);
        $this->applyAgencyServiceFilters($qb, "daf", $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($qb, "daf", $criteria);
        $this->applyFilterAppro($qb, "daf", $estAppro, $estAdmin);
        $this->applyStatutsFilters($qb, "daf", $criteria);

        // 4️⃣ Compter le total distinct des DA après filtrage
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT daf.numeroDemandeAppro) as total');
        $totalItems = (int)$countQb->getQuery()->getSingleScalarResult();

        // 5️⃣ Pagination sur les DA distincts
        $distinctQb = clone $qb;
        $distinctQb
            ->select('daf.numeroDemandeAppro')
            ->groupBy('daf.numeroDemandeAppro');
        $this->handleOrderBy($distinctQb, 'daf', $criteria, true);
        $distinctQb
            ->addOrderBy('MAX(daf.numeroDemandeAppro)', 'DESC')
            ->addOrderBy('MAX(daf.numeroFournisseur)', 'DESC')
            ->addOrderBy('MAX(daf.numeroCde)', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $numeroDAsPage = array_column($distinctQb->getQuery()->getResult(), 'numeroDemandeAppro');

        if (empty($numeroDAsPage)) {
            return [
                'results' => [],
                'totalItems' => $totalItems
            ];
        }

        // 6️⃣ Charger les dernières versions uniquement pour les DA de la page
        $finalQb = $this->createQueryBuilder('daf')
            ->where('daf.numeroDemandeAppro IN (:numeroDAsPage)')
            ->andWhere('daf.deleted = 0')
            ->setParameter('numeroDAsPage', $numeroDAsPage);
        $this->handleOrderBy($finalQb, 'daf', $criteria);
        $finalQb
            ->addOrderBy('daf.numeroDemandeAppro', 'DESC')
            ->addOrderBy('daf.numeroFournisseur', 'DESC')
            ->addOrderBy('daf.numeroCde', 'DESC');

        // Limiter aux numéros de version max
        $orX = $finalQb->expr()->orX();
        foreach ($versionsMax as $numeroDA => $versionMax) {
            if (in_array($numeroDA, $numeroDAsPage)) {
                $orX->add($finalQb->expr()->andX(
                    $finalQb->expr()->eq('daf.numeroDemandeAppro', ':da_' . $numeroDA),
                    $finalQb->expr()->eq('daf.numeroVersion', ':ver_' . $numeroDA)
                ));
                $finalQb->setParameter('da_' . $numeroDA, $numeroDA);
                $finalQb->setParameter('ver_' . $numeroDA, $versionMax);
            }
        }
        $finalQb->andWhere($orX);

        $results = $finalQb->getQuery()->getResult();

        return [
            'results'    => $results,
            'totalItems' => $totalItems
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
        $paginated = $this->getFilteredLastVersions($user, $criteria, $idAgenceUser, $estAppro, $estAtelier, $estAdmin, $page, $limit);

        $totalItems = $paginated['totalItems'];
        $lastPage = $totalItems > 0 ? ceil($totalItems / $limit) : 0;

        return [
            'data'        => $paginated['results'],
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage
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
                    DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
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
                'numDa'        => "$qbLabel.numeroDemandeAppro",
                'numDit'       => "$qbLabel.numeroDemandeDit",
                'numCde'       => "$qbLabel.numeroCde",
                'numOr'        => "$qbLabel.numeroOr",
                'numFrn'       => "$qbLabel.numeroFournisseur",
                'frn'          => "$qbLabel.nomFournisseur",
            ];
        } else {
            $map = [
                'numDa'         => "$qbLabel.numeroDemandeAppro",
                'numDit'        => "$qbLabel.numeroDemandeDit",
                'demandeur'     => "$qbLabel.demandeur",
                'codeCentrale'  => "$qbLabel.codeCentrale",
                'niveauUrgence' => "$qbLabel.niveauUrgence"
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
        } else {
            if (!empty($criteria['statutDA'])) {
                $queryBuilder->andWhere($qbLabel . '.statutDal = :statutDa')
                    ->setParameter('statutDa', $criteria['statutDA']);
            } else {
                $queryBuilder->andWhere($qbLabel . '.statutDal != :statutDa')
                    ->setParameter('statutDa', DemandeAppro::STATUT_TERMINER);
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
                ->setParameter('agEmet', $criteria['agenceEmetteur']->getId());
        }
        if (!empty($criteria['serviceEmetteur'])) {
            $qb->andWhere("$qbLabel.serviceEmetteur = :agServEmet")
                ->setParameter('agServEmet', $criteria['serviceEmetteur']->getId());
        }


        if (!empty($criteria['agenceDebiteur'])) {
            $qb->andWhere("$qbLabel.agenceDebiteur = :agDebit")
                ->setParameter('agDebit', $criteria['agenceDebiteur']->getId())
            ;
        }

        if (!empty($criteria['serviceDebiteur'])) {
            $qb->andWhere("$qbLabel.serviceDebiteur = :serviceDebiteur")
                ->setParameter('serviceDebiteur', $criteria['serviceDebiteur']->getId());
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
        );

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
}
