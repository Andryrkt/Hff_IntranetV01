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

    public function getConstructeurRefDesi(): array
{
    $result = $this->createQueryBuilder('dav')
        ->select("CONCAT(dav.artConstp, '_', dav.artRef, '_', dav.artDesi) AS refDesi")
        ->getQuery()
        ->getScalarResult();

    return array_column($result, 'refDesi');
}
    
}
