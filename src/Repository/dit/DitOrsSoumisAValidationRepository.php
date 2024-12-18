<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitOrsSoumisAValidationRepository extends EntityRepository
{

    public function findNumOrItvValide()
    {
        $query = $this->createQueryBuilder('osv')
            ->select("DISTINCT CONCAT(osv.numeroOR, '-', osv.numeroItv) AS numeroORNumeroItv")
            ->where('osv.statut IN (:statut)')
            ->setParameter('statut', ['Validé', 'Livré', 'Livré partiellement'])
            ->getQuery()
            ->getSingleColumnResult();

        return $query;
    }

    public function findNbrItv($numOr)
    {
        $nbrItv = $this->createQueryBuilder('osv')
            ->select('COUNT(osv.numeroItv)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        return $nbrItv ? $nbrItv : 0;
    }

    public function findNumItvValide($numOr)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        $statut = ['Validé', 'Livré'];

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le numero d'intervention
        $nbrItv = $this->createQueryBuilder('osv')
            ->select('osv.numeroItv')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.statut IN (:statut)')
            ->andwhere('osv.numeroVersion = :numeroVersionMax')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'statut' => $statut,
            ])
            ->getQuery()
            ->getSingleColumnResult();

        return $nbrItv;
    }


    public function findStatutByNumeroVersionMax($numOr, $numItv)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $statut = $this->createQueryBuilder('osv')
            ->select('osv.statut')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroItv = :numItv')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'numItv' => $numItv,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $statut;
    }


    public function findNumeroVersionMax($numOr)
    {
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    public function findOrSoumiAvant($numOr)
    {
        $qb = $this->createQueryBuilder('osv');

        $subquery = $this->createQueryBuilder('osv2')
            ->select('MAX(osv2.numeroVersion)')
            ->where('osv2.numeroOR = :numOr')
            ->getDQL();

        $orSoumisAvant = $qb
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->andWhere($qb->expr()->eq('osv.numeroVersion', '(' . $subquery . ')'))
            ->getQuery()
            ->getResult();

        return $orSoumisAvant;
    }

    public function findOrSoumiAvantMax($numOr)
    {
        // Étape 1: Récupérer la version maximale pour le numeroOR donné
        $qbMax = $this->createQueryBuilder('osv2')
            ->select('MAX(osv2.numeroVersion)')
            ->where('osv2.numeroOR = :numOr')
            ->setParameter('numOr', $numOr);

        $maxVersion = $qbMax->getQuery()->getSingleScalarResult();

        if ($maxVersion === null || $maxVersion == 1) {
            // Si la version max est 1 ou nulle, il n'y a pas de version avant la version maximale
            return null;
        }

        // Étape 2: Récupérer la ligne qui a la version juste avant la version max
        $qb = $this->createQueryBuilder('osv')
            ->where('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroVersion = :previousVersion')
            ->setParameter('numOr', $numOr)
            ->setParameter('previousVersion', $maxVersion - 1)  // Juste avant la version max
            ->getQuery()
            ->getResult();

        return $qb;
    }


    public function findMontantValide($numOr, $numItv)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $montantValide = $this->createQueryBuilder('osv')
            ->select('osv.montantItv')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->andWhere('osv.numeroItv = :numItv')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
                'numItv' => $numItv,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $montantValide;
    }

    public function findOrSoumisValid($numOr)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('osv')
            ->select('MAX(osv.numeroVersion)')
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        $montantValide = $this->createQueryBuilder('osv')
            ->where('osv.numeroVersion = :numeroVersionMax')
            ->andWhere('osv.numeroOR = :numOr')
            ->setParameters([
                'numeroVersionMax' => $numeroVersionMax,
                'numOr' => $numOr,
            ])
            ->getQuery()
            ->getResult();

        return $montantValide;
    }
}
