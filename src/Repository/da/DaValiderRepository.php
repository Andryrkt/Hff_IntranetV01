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
    public function getNumeroVersionMaxDit(string $numeroDit): int
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

    public function getDaValider($numeroVersion, $numeroDemandeDit, $reference, $criteria = [])
    {
        $davalider =  $this->createQueryBuilder('d')
            ->where('d.numeroVersion = :version')
            ->andWhere('d.numeroDemandeDit = :numDit')
            ->andWhere('d.artRefp = :ref')
            ->setParameter('version', $numeroVersion)
            ->setParameter('ref', $reference)
            ->setParameter('numDit', $numeroDemandeDit);
        if (empty($criteria['numDa'])) {
            $davalider->andWhere('d.statutDal != :statut')
                ->setParameter('statut', 'TERMINER');
        }
        return $davalider
            ->getQuery()
            ->getOneOrNullResult();
    }
}
