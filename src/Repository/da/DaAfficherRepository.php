<?php

namespace App\Repository\da;

use App\Entity\da\DaAfficher;
use Doctrine\ORM\EntityRepository;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Da\DaValider;

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
        $numeroVersionMax = $this->createQueryBuilder('dav')
            ->select('DISTINCT MAX(dav.numeroVersion)')
            ->where('dav.numeroCde = :numCde')
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
        $numeroVersionMax = $this->createQueryBuilder('dav')
            ->select('DISTINCT MAX(dav.numeroVersion)')
            ->where('dav.numeroDemandeDit = :numDit')
            ->setParameter('numDit', $numeroDit)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return 0;
        }
        return $numeroVersionMax;
    }

    public function getDaValider($numeroVersion, $numeroDemandeDit, $reference, $designation, $criteria = [])
    {
        $davalider =  $this->createQueryBuilder('d')
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
            $davalider->andWhere('d.statutDal != :statut')
                ->setParameter('statut', 'TERMINER');
        }

        // $query = $davalider->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }
        return $davalider
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getSumQteDemEtLivrer(string $numDa): array
    {
        $numeroVersionMax = $this->createQueryBuilder('dav')
            ->select('MAX(dav.numeroVersion)')
            ->where('dav.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult();
        if ($numeroVersionMax === null) {
            return [
                'qteDem' => 0,
                'qteLivrer' => 0
            ];
        }
        $qb = $this->createQueryBuilder('dav')
            ->select('SUM(dav.qteDem) as qteDem, SUM(dav.qteLivrer) as qteLivrer')
            ->where('dav.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->andWhere('dav.numeroVersion = :numVersion')
            ->setParameter('numVersion', $numeroVersionMax);

        return $qb->getQuery()->getSingleResult();
    }

    public function getConstructeurRefDesi(): array
    {
        $result = $this->createQueryBuilder('dav')
            ->select("CONCAT(dav.artConstp, '_', dav.artRef, '_', dav.artDesi) AS refDesi")
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'refDesi');
    }


    public function getDaOrValider(array $numOrValideZst, ?array $criteria): array
    {
        // Étape 1 : récupérer pour chaque OR la version maximale avec statut "validé"
        $subQb = $this->_em->createQueryBuilder();
        $subQb->select('dav.numeroOr', 'MAX(dav.numeroVersion) AS maxVersion', 'dav.numeroDemandeAppro')
            ->from(DaValider::class, 'dav')
            ->where('dav.statutDal = :statutValide')
            ->groupBy('dav.numeroOr', 'dav.numeroDemandeAppro')
            ->setParameter('statutValide', DemandeAppro::STATUT_VALIDE);

        $latestVersions = $subQb->getQuery()->getArrayResult();

        if (empty($latestVersions)) {
            return [];
        }

        // Étape 2 : requête principale avec conditions sur les couples (numeroOr, version, numeroDemandeAppro)
        $qb = $this->_em->createQueryBuilder();
        $qb->select('dav')
            ->from(DaValider::class, 'dav')
            ->where('dav.statutDal = :statutValide')
            ->setParameter('statutValide', DemandeAppro::STATUT_VALIDE);

        $orX = $qb->expr()->orX();

        foreach ($latestVersions as $i => $entry) {
            if (!empty($numOrValideZst) && !in_array($entry['numeroOr'], $numOrValideZst)) {
                continue;
            }

            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('dav.numeroOr', ':numeroOr_' . $i),
                    $qb->expr()->eq('dav.numeroVersion', ':version_' . $i),
                    $qb->expr()->eq('dav.numeroDemandeAppro', ':numeroDemandeAppro_' . $i)
                )
            );

            $qb->setParameter('numeroOr_' . $i, $entry['numeroOr']);
            $qb->setParameter('version_' . $i, $entry['maxVersion']);
            $qb->setParameter('numeroDemandeAppro_' . $i, $entry['numeroDemandeAppro']);
        }

        if ($orX->count() === 0) {
            return [];
        }

        $qb->andWhere($orX);

        // Étape 3 : appliquer des filtres dynamiques s'ils existent
        $this->applyDynamicFilters($qb, $criteria);

        $qb->orderBy('dav.numeroDemandeAppro', 'ASC');
        return $qb->getQuery()->getResult();
    }


    private function applyDynamicFilters(QueryBuilder $qb, ?array $criteria): void
    {
        if (empty($criteria)) {
            return;
        }

        $map = [
            'numDa' => 'dav.numeroDemandeAppro',
            'numDit' => 'dav.numeroDemandeDit',
            'numFrn' => 'dav.numeroFournisseur',
            'statutBc' => 'dav.statutCde',
            'niveauUrgence' => 'dav.niveauUrgence',
        ];

        foreach ($map as $key => $field) {
            if (!empty($criteria[$key])) {
                $qb->andWhere("$field = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
        }

        if (!empty($criteria['ref'])) {
            $qb->andWhere('dav.artRefp LIKE :ref')
                ->setParameter('ref', '%' . $criteria['ref'] . '%');
        }

        if (!empty($criteria['designation'])) {
            $qb->andWhere('dav.artDesi LIKE :designation')
                ->setParameter('designation', '%' . $criteria['designation'] . '%');
        }

        if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
            $qb->andWhere('dav.datePlannigOr >= :dateDebutOR')
                ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
        }

        if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
            $qb->andWhere('dav.datePlannigOr <= :dateFinOR')
                ->setParameter('dateFinOR', $criteria['dateFinOR']);
        }

        if (!empty($criteria['dateDebutDAL']) && $criteria['dateDebutDAL'] instanceof \DateTimeInterface) {
            $qb->andWhere('dav.dateFinSouhaite >= :dateDebutDAL')
                ->setParameter('dateDebutDAL', $criteria['dateDebutDAL']);
        }

        if (!empty($criteria['dateFinDAL']) && $criteria['dateFinDAL'] instanceof \DateTimeInterface) {
            $qb->andWhere('dav.dateFinSouhaite <= :dateFinDAL')
                ->setParameter('dateFinDAL', $criteria['dateFinDAL']);
        }
    }

    public function findDerniereVersionDesDA(): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb->where(
                'd.numeroVersion = (
                    SELECT MAX(d2.numeroVersion)
                    FROM ' . DaAfficher::class . ' d2
                    WHERE d2.numeroDemandeAppro = d.numeroDemandeAppro
                )'
            );

        return $qb->getQuery()->getResult();
    }
}
