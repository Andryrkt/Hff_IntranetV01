<?php

namespace App\Repository\da;

use App\Entity\da\DaAfficher;
use Doctrine\ORM\QueryBuilder;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitOrsSoumisAValidation;
use Doctrine\DBAL\ArrayParameterType;

class DaAfficherRepository extends EntityRepository
{
    /**
     *  Récupère le numéro de version maximum pour une demande d'approvisionnement (DA) donnée.
     *
     * @param string $numeroDemandeAppro
     * @return void
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


    public function getDaOrValider(?array $criteria = []): array
    {
        if ($criteria == null) $criteria = [];

        // 1. Vérifier si le champ achatDirecte existe dans l'entité
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

        // Condition pour statutOR seulement si le champ existe et est false
        if ($hasAchatDirecte) {
            $qb->andWhere('
            (d.achatDirect = true OR 
            (d.achatDirect = false AND d.statutOr = :statutOR))
        ')->setParameter('statutOR', DitOrsSoumisAValidation::STATUT_VALIDE);
        } else {
            // Si le champ n'existe pas, on applique seulement le statutOR
            $qb->andWhere('d.statutOr = :statutOR')
                ->setParameter('statutOR', DitOrsSoumisAValidation::STATUT_VALIDE);
        }

        // Condition pour les versions maximales
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

        // 4. Appliquer les filtres dynamiques
        $this->applyDynamicFilters($qb, $criteria, true);
        $this->applyStatutsFilters($qb, $criteria, true);
        $this->applyDateFilters($qb, $criteria, true);

        $qb->orderBy('d.dateDemande', 'DESC');

        return $qb->getQuery()->getResult();
    }


    public function findDerniereVersionDesDA(User $user, array $criteria,  int $idAgenceUser, bool $estAppro, bool $estAtelier): array
    {
        $estAdmin = in_array(Role::ROLE_ADMINISTRATEUR, $user->getRoleIds());

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

        $qb->addOrderBy('d.dateDemande', 'DESC');
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
                'niveauUrgence' => 'd.niveauUrgence',
            ];
        } else {
            $map = [
                'numDa' => 'd.numeroDemandeAppro',
                'numDit' => 'd.numeroDemandeDit',
                'niveauUrgence' => 'd.niveauUrgence',
                'demandeur' => 'd.demandeur'
            ];
        }


        foreach ($map as $key => $field) {
            if (!empty($criteria[$key])) {
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
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
            if (!empty($criteria['statutBC'])) {
                $queryBuilder->andWhere('d.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBC']);
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
}
