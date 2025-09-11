<?php

namespace App\Repository\da;

use App\Entity\da\DaAfficher;
use Doctrine\ORM\QueryBuilder;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use Doctrine\DBAL\ArrayParameterType;

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

    public function markAsDeletedByNumeroLigne(string $numeroDemandeAppro, array $numeroLignes, string $userName): void
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
                'version'   => $this->getNumeroVersionMax($numeroDemandeAppro),
                'deleted'   => true,
                'deletedBy' => $userName,
                'lines'     => $numeroLignes,
            ])
            ->getQuery()
            ->execute();
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

        // Vérifier si achatDirect existe dans l'entité
        $classMetadata   = $this->_em->getClassMetadata(DaAfficher::class);
        $hasAchatDirecte = $classMetadata->hasField('achatDirect');

        // ----------------------
        // 1. Sous-requête : versions maximales
        // ----------------------
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select('d.numeroDemandeAppro', 'MAX(d.numeroVersion) as maxVersion')
            ->from(DaAfficher::class, 'd')
            ->groupBy('d.numeroDemandeAppro');


        // Liste des exceptions pour lesquelles statutOr n'est pas requis
        $exceptions = [
            'DAP25079981'
        ];
        // Condition générique sur statutOr avec exceptions
        $orCondition = $subQb->expr()->orX(
            $subQb->expr()->eq('d.statutOr', ':statutOR'),
            $subQb->expr()->in('d.numeroDemandeAppro', ':exceptions')
        );
        // Appliquer la condition selon la présence de achatDirect
        if ($hasAchatDirecte) {
            $subQb->andWhere('d.achatDirect = true OR (d.achatDirect = false AND (' . $orCondition . '))');
        } else {
            $subQb->andWhere($orCondition);
        }
        // Paramètres communs
        $subQb->setParameter('statutOR', DitOrsSoumisAValidation::STATUT_VALIDE)
            ->setParameter('exceptions', $exceptions);


        $this->applyDynamicFilters($subQb, $criteria, true);
        $this->applyStatutsFilters($subQb, $criteria, true);
        $this->applyDateFilters($subQb, $criteria, true);

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
        ;

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

        // Ordre final
        $qb->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroFournisseur', 'DESC')
            ->addOrderBy('d.numeroCde', 'DESC');
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

    public function getDaOrValider(?array $criteria = []): array
    {
        // Toujours forcer en tableau
        $criteria = $criteria ?? [];

        // 1. Vérifier si le champ achatDirect existe dans l'entité
        $classMetadata = $this->_em->getClassMetadata(DaAfficher::class);
        $hasAchatDirecte = $classMetadata->hasField('achatDirect');

        // 2. Récupérer les versions maximales validées
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select('d.numeroDemandeAppro', 'MAX(d.numeroVersion) as maxVersion')
            ->from(DaAfficher::class, 'd')
            ->where('d.statutDal = :statutValide')
            ->groupBy('d.numeroDemandeAppro');

        $latestVersions = $subQb->getQuery()
            ->setParameter('statutValide', DemandeAppro::STATUT_VALIDE)
            ->getArrayResult();

        if (empty($latestVersions)) {
            return [];
        }

        // 3. Construire la requête principale
        $qb = $this->_em->createQueryBuilder();
        $qb->select('d')
            ->from(DaAfficher::class, 'd')
            ->where('d.statutDal = :statutDa')
            ->setParameter('statutDa', DemandeAppro::STATUT_VALIDE);

        // Liste des exceptions pour lesquelles statutOr n'est pas requis
        $exceptions = [
            'DAP25079981'
        ];

        // Condition générique sur statutOr avec exceptions
        $orCondition = $qb->expr()->orX(
            $qb->expr()->eq('d.statutOr', ':statutOR'),
            $qb->expr()->in('d.numeroDemandeAppro', ':exceptions')
        );

        // Appliquer la condition selon la présence de achatDirect
        if ($hasAchatDirecte) {
            $qb->andWhere('d.achatDirect = true OR (d.achatDirect = false AND (' . $orCondition . '))');
        } else {
            $qb->andWhere($orCondition);
        }

        // Paramètres communs
        $qb->setParameter('statutOR', DitOrsSoumisAValidation::STATUT_VALIDE)
            ->setParameter('exceptions', $exceptions);

        // 4. Condition pour les versions maximales
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

        // 5. Appliquer les filtres dynamiques
        $this->applyDynamicFilters($qb, $criteria, true);
        $this->applyStatutsFilters($qb, $criteria, true);
        $this->applyDateFilters($qb, $criteria, true);

        // 6. Tri
        $qb->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroFournisseur', 'DESC')
            ->addOrderBy('d.numeroCde', 'DESC');

        return $qb->getQuery()->getResult();
    }

    private function getPaginatedDas(User $user, array $criteria,  int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin, int $page, int $limit): array
    {
        $subQb = $this->createQueryBuilder('d')
            ->select('d.numeroDemandeAppro, MAX(d.numeroVersion) AS maxVersion')
            ->where('d.deleted = 0')
            ->groupBy('d.numeroDemandeAppro');

        $this->applyDynamicFilters($subQb, $criteria);
        $this->applyAgencyServiceFilters($subQb, $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($subQb, $criteria);
        $this->applyFilterAppro($subQb, $estAppro, $estAdmin);
        $this->applyStatutsFilters($subQb, $criteria);

        // ⚡ Cloner avant de limiter les résultats
        $countQb = clone $subQb;
        $countQb->resetDQLPart('orderBy'); // pas besoin d'ORDER BY dans un COUNT

        // Nombre total de DA distincts
        $totalItems = count($countQb->getQuery()->getResult());

        // Pagination + order by
        $subQb->orderBy('MAX(d.dateDemande)', 'DESC')
            ->addOrderBy('MAX(d.numeroFournisseur)', 'DESC')
            ->addOrderBy('MAX(d.numeroCde)', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'results'    => $subQb->getQuery()->getArrayResult(),
            'totalItems' => $totalItems
        ];
    }

    /**
     * fonction Pour récupérer les données filtrées
     */
    public function findPaginatedAndFilteredDA(User $user, array $criteria,  int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin, int $page, int $limit)
    {
        $paginatedDAs = $this->getPaginatedDas($user, $criteria, $idAgenceUser, $estAppro, $estAtelier, $estAdmin, $page, $limit);
        $numeroDAsPage = array_column($paginatedDAs['results'], 'numeroDemandeAppro');
        $versionsMax = array_column($paginatedDAs['results'], 'maxVersion', 'numeroDemandeAppro');

        if (empty($numeroDAsPage)) {
            return [
                'data'        => [],
                'totalItems'  => 0,
                'currentPage' => $page,
                'lastPage'    => 0,
            ];
        }

        $qb = $this->createQueryBuilder('daf')
            ->where('daf.numeroDemandeAppro IN (:numeroDAs)')
            ->where('daf.deleted = 0')
            ->setParameter('numeroDAs', $numeroDAsPage);

        // Ajouter condition version max (évite duplications)
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

        $qb->orderBy('daf.dateDemande', 'DESC')
            ->addOrderBy('daf.numeroFournisseur', 'DESC')
            ->addOrderBy('daf.numeroCde', 'DESC');

        $totalItems = $paginatedDAs['totalItems'];
        $lastPage = ceil($totalItems / $limit);

        return [
            'data'        => $qb->getQuery()->getResult(),
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
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


        $this->applyDynamicFilters($qb, $criteria);
        $this->applyAgencyServiceFilters($qb, $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);
        $this->applyDateFilters($qb, $criteria);

        $this->applyFilterAppro($qb, $estAppro, $estAdmin);
        $this->applyStatutsFilters($qb, $criteria);

        $qb->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroFournisseur', 'DESC')
            ->addOrderBy('d.numeroCde', 'DESC');
        return $qb->getQuery()->getResult();
    }

    private function applyFilterAppro(QueryBuilder $qb, bool $estAppro, bool $estAdmin): void
    {
        if (!$estAdmin && $estAppro) {
            $qb->andWhere('d.statutDal IN (:authorizedStatuts)')
                ->setParameter('authorizedStatuts', [
                    DemandeAppro::STATUT_SOUMIS_APPRO,
                    DemandeAppro::STATUT_SOUMIS_ATE,
                    DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
                    DemandeAppro::STATUT_VALIDE,
                    DemandeAppro::STATUT_TERMINER
                ], ArrayParameterType::STRING);
        }
    }

    private function applyDynamicFilters(QueryBuilder $qb, array $criteria, bool $estCdeFrn = false): void
    {
        if ($estCdeFrn) {
            $map = [
                'numDa' => 'd.numeroDemandeAppro',
                'numDit' => 'd.numeroDemandeDit',
                'numCde' => 'd.numeroCde',
                'numOr' => 'd.numeroOr',
                'numFrn' => 'd.numeroFournisseur',
                'frn' => 'd.nomFournisseur',
            ];
        } else {
            $map = [
                'numDa' => 'd.numeroDemandeAppro',
                'numDit' => 'd.numeroDemandeDit',
                'demandeur' => 'd.demandeur'
            ];
        }


        foreach ($map as $key => $field) {
            if (!empty($criteria[$key])) {
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
        }

        if (empty($criteria['numDit']) && empty($criteria['numDa'])) {
            $qb->leftJoin('d.dit', 'dit')
                ->leftJoin('dit.idStatutDemande', 'statut')
                ->andWhere('d.dit IS NULL OR statut.id NOT IN (:clotureStatut)')
                ->setParameter('clotureStatut', [
                    DemandeIntervention::STATUT_CLOTUREE_ANNULEE,
                    DemandeIntervention::STATUT_CLOTUREE_HORS_DELAI
                ]);
        }

        if (!empty($criteria['niveauUrgence'])) {
            $qb->andWhere("d.niveauUrgence = :niveau")
                ->setParameter("niveau", $criteria['niveauUrgence']->getDescription());
        }

        if (!empty($criteria['ref'])) {
            $qb->andWhere('d.artRefp LIKE :ref')
                ->setParameter('ref', '%' . $criteria['ref'] . '%');
        }

        if (!empty($criteria['designation'])) {
            $qb->andWhere('d.artDesi LIKE :designation')
                ->setParameter('designation', '%' . $criteria['designation'] . '%');
        }

        if (!empty($criteria['typeAchat']) && $criteria['typeAchat'] !== 'tous') {
            $typeAchat = $criteria['typeAchat'] === 'direct' ? 1 : 0;
            $qb->andWhere('d.achatDirect = :typeAchat')
                ->setParameter('typeAchat', $typeAchat);
        }
    }

    private function applyStatutsFilters(QueryBuilder $queryBuilder, array $criteria, bool $estCdeFrn = false)
    {
        if ($estCdeFrn) {
            if (!empty($criteria['statutBc'])) {
                $queryBuilder->andWhere('d.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBc']);
            }
        } else {
            if (!empty($criteria['statutDA'])) {
                $queryBuilder->andWhere('d.statutDal = :statutDa')
                    ->setParameter('statutDa', $criteria['statutDA']);
            } else {
                $queryBuilder->andWhere('d.statutDal != :statutDa')
                    ->setParameter('statutDa', DemandeAppro::STATUT_TERMINER);
            }

            if (!empty($criteria['statutOR'])) {
                $queryBuilder->andWhere('d.statutOr = :statutOr')
                    ->setParameter('statutOr', $criteria['statutOR']);
            }

            if (!empty($criteria['statutBC'])) {
                $queryBuilder->andWhere('d.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBC']);
            }
        }
    }


    private function applyDateFilters($qb, array $criteria, bool $estCdeFrn = false)
    {
        if ($estCdeFrn) {
            /** Date fin souhaite */
            if (!empty($criteria['dateDebutfinSouhaite']) && $criteria['dateDebutfinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }

            if (!empty($criteria['dateFinFinSouhaite']) && $criteria['dateFinFinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }

            /** DATE PLANNING OR */
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }

            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }
        } else {
            /** Date fin souhaite */
            if (!empty($criteria['dateDebutfinSouhaite']) && $criteria['dateDebutfinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateFinSouhaite >= :dateDebutfinSouhaite')
                    ->setParameter('dateDebutfinSouhaite', $criteria['dateDebutfinSouhaite']);
            }

            if (!empty($criteria['dateFinFinSouhaite']) && $criteria['dateFinFinSouhaite'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateFinSouhaite <= :dateFinFinSouhaite')
                    ->setParameter('dateFinFinSouhaite', $criteria['dateFinFinSouhaite']);
            }

            /** Date DA (date de demande) */
            if (!empty($criteria['dateDebutCreation']) && $criteria['dateDebutCreation'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateDemande >= :dateDemandeDebut')
                    ->setParameter('dateDemandeDebut', $criteria['dateDebutCreation']);
            }

            if (!empty($criteria['dateFinCreation']) && $criteria['dateFinCreation'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.dateDemande <= :dateDemandeFin')
                    ->setParameter('dateDemandeFin', $criteria['dateFinCreation']);
            }

            /** DATE PLANNING OR */
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }

            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('d.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }
        }
    }

    private function applyAgencyServiceFilters($qb, array $criteria, User $user, int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin)
    {
        if (!$estAtelier && !$estAppro && !$estAdmin) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'da.agenceDebiteur IN (:agenceAutoriserIds)',
                        'da.agenceEmetteur = :codeAgence'
                    )
                )
                ->setParameter('agenceAutoriserIds', $user->getAgenceAutoriserIds(), ArrayParameterType::INTEGER)
                ->setParameter('codeAgence', $idAgenceUser)
                ->andWhere(
                    $qb->expr()->orX(
                        'da.serviceDebiteur IN (:serviceAutoriserIds)',
                        'da.serviceEmetteur IN (:serviceAutoriserIds)'
                    )
                )
                ->setParameter('serviceAutoriserIds', $user->getServiceAutoriserIds(), ArrayParameterType::INTEGER);
        }

        if (!empty($criteria['agenceEmetteur'])) {
            $qb->andWhere('d.agenceEmetteurId = :agEmet')
                ->setParameter('agEmet', $criteria['agenceEmetteur']->getId());
        }
        if (!empty($criteria['serviceEmetteur'])) {
            $qb->andWhere('d.serviceEmetteurId = :agServEmet')
                ->setParameter('agServEmet', $criteria['serviceEmetteur']->getId());
        }


        if (!empty($criteria['agenceDebiteur'])) {
            $qb->andWhere('d.agenceDebiteurId = :agDebit')
                ->setParameter('agDebit', $criteria['agenceDebiteur']->getId())
            ;
        }

        if (!empty($criteria['serviceDebiteur'])) {
            $qb->andWhere('d.serviceDebiteurId = :serviceDebiteur')
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
     * @return string
     */
    public function getLastStatutDaAfficher(string $numeroDemandeAppro): string
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
            ->getSingleScalarResult();
    }
}
