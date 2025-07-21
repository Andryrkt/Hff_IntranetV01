<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaValiderRepository extends EntityRepository
{
    /**
     *  Récupère le numéro de version maximum pour une demande d'approvisionnement (DA) donnée.
     *
     * @param string $numeroDemandeAppro
     * @return void
     */
    public function getNumeroVersionMax(string $numeroDemandeAppro)
    {
        $numeroVersionMax = $this->createQueryBuilder('dav')
            ->select('MAX(dav.numeroVersion)')
            ->where('dav.numeroDemandeAppro = :numDa')
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
            ->setParameter('version', $numeroVersion)
            ->setParameter('ref', $reference)
            ->setParameter('desi', $designation)
            ->setParameter('numDit', $numeroDemandeDit);
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

    public function getDaOrValider(array $numOrValideZst, ?array $criteria): array
    {
        // Étape 1 : sous-requête pour récupérer (numeroOr, maxVersion)
        $subQuery = $this->_em->createQueryBuilder()
            ->select('dav2.numeroOr AS numeroOr', 'MAX(dav2.numeroVersion) AS maxVersion')
            ->from('App\Entity\Da\DaValider', 'dav2')
            ->where('dav2.numeroOr IN (:numOrValideZst)')
            ->groupBy('dav2.numeroOr')
            ->setParameter('numOrValideZst', $numOrValideZst);

        $resultats = $subQuery->getQuery()->getArrayResult();

        if (empty($resultats)) {
            return [];
        }

        // Étape 2 : construire les critères pour récupérer les lignes finales
        $criteres = [];
        foreach ($resultats as $res) {
            $criteres[] = [
                'numeroOr' => $res['numeroOr'],
                'numeroVersion' => $res['maxVersion'],
            ];
        }

        // Étape 3 : requête finale avec conditions dynamiques
        $qb = $this->createQueryBuilder('dav');
        $orX = $qb->expr()->orX();

        foreach ($criteres as $index => $critere) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('dav.numeroOr', ':numeroOr_' . $index),
                    $qb->expr()->eq('dav.numeroVersion', ':version_' . $index)
                )
            );
            $qb->setParameter('numeroOr_' . $index, $critere['numeroOr']);
            $qb->setParameter('version_' . $index, $critere['numeroVersion']);
        }

        $qb->where($orX);

        // Étape 4 : ajout des filtres dynamiques
        if (!empty($criteria)) {
            if (!empty($criteria['numDa'])) {
                $qb->andWhere('dav.numeroDemandeAppro = :numDa')
                    ->setParameter('numDa', $criteria['numDa']);
            }

            if (!empty($criteria['numDit'])) {
                $qb->andWhere('dav.numeroDemandeDit = :numDit')
                    ->setParameter('numDit', $criteria['numDit']);
            }

            if (!empty($criteria['numFrn'])) {
                $qb->andWhere('dav.numeroFournisseur = :numFrn')
                    ->setParameter('numFrn', $criteria['numFrn']);
            }

            if (!empty($criteria['ref'])) {
                $qb->andWhere('dav.artRefp LIKE :ref')
                    ->setParameter('ref', '%' . $criteria['ref'] . '%');
            }

            if (!empty($criteria['designation'])) {
                $qb->andWhere('dav.artDesi LIKE :designation')
                    ->setParameter('designation', '%' . $criteria['designation'] . '%');
            }

            if (!empty($criteria['statutBc'])) {
                $qb->andWhere('dav.statutCde = :statutBc')
                    ->setParameter('statutBc', $criteria['statutBc']);
            }

            if (!empty($criteria['niveauUrgence'])) {
                $qb->andWhere('dav.niveauUrgence = :niveauUrgence')
                    ->setParameter('niveauUrgence', $criteria['niveauUrgence']);
            }

            /** ## Date planning OR ## */
            if (!empty($criteria['dateDebutOR']) && $criteria['dateDebutOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('dav.datePlannigOr >= :dateDebutOR')
                    ->setParameter('dateDebutOR', $criteria['dateDebutOR']);
            }

            if (!empty($criteria['dateFinOR']) && $criteria['dateFinOR'] instanceof \DateTimeInterface) {
                $qb->andWhere('dav.datePlannigOr <= :dateFinOR')
                    ->setParameter('dateFinOR', $criteria['dateFinOR']);
            }

            /** ## Date fin sohaite ## */
            if (!empty($criteria['dateDebutDAL']) && $criteria['dateDebutDAL'] instanceof \DateTimeInterface) {
                $qb->andWhere('dav.dateFinSouhaite >= :dateDebutDAL')
                    ->setParameter('dateDebutDAL', $criteria['dateDebutDAL']);
            }

            if (!empty($criteria['dateFinDAL']) && $criteria['dateFinDAL'] instanceof \DateTimeInterface) {
                $qb->andWhere('dav.dateFinSouhaite <= :dateFinDAL')
                    ->setParameter('dateFinDAL', $criteria['dateFinDAL']);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
