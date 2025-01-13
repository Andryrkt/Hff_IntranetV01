<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitDevisSoumisAValidationRepository extends EntityRepository
{

    public function findDernierStatutDevis($numDevis)
    {
        // Étape 1 et 2 combinées : Récupérer directement le statut correspondant au numeroVersion maximum
        $queryBuilder = $this->createQueryBuilder('dev');
        
        $dernierStatut = $queryBuilder
            ->select('dev.statut')
            ->where('dev.numeroDevis = :numDevis')
            ->andWhere('dev.numeroVersion = (
                SELECT MAX(dev2.numeroVersion) 
                FROM App\Entity\dit\DitDevisSoumisAValidation dev2 
                WHERE dev2.numeroDevis = :numDevis
            )')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getOneOrNullResult(); // Retourne null si aucun résultat

        // Retourne le statut ou null si rien n'est trouvé
        return $dernierStatut ? $dernierStatut['statut'] : null;
    }

    public function findDevisSoumiAvant($numDevis)
    {
        $qb = $this->createQueryBuilder('dev');

        $subquery = $this->createQueryBuilder('dev2')
            ->select('MAX(dev2.numeroVersion)')
            ->where('dev2.numeroDevis = :numDevis')
            ->getDQL();

        $devSoumisAvant = $qb
            ->where('dev.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->andWhere($qb->expr()->eq('dev.numeroVersion', '(' . $subquery . ')'))
            ->getQuery()
            ->getResult();

        return $devSoumisAvant;
    }

    public function findDevisSoumiAvantMax($numDevis)
    {
        // Étape 1: Récupérer la version maximale pour le numeroDevis donné
        $qbMax = $this->createQueryBuilder('dev2')
            ->select('MAX(dev2.numeroVersion)')
            ->where('dev2.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis);

        $maxVersion = $qbMax->getQuery()->getSingleScalarResult();

        if ($maxVersion === null || $maxVersion == 1) {
            // Si la version max est 1 ou nulle, il n'y a pas de version avant la version maximale
            return null;
        }

        // Étape 2: Récupérer la ligne qui a la version juste avant la version max
        $qb = $this->createQueryBuilder('dev')
            ->where('dev.numeroDevis = :numDevis')
            ->andWhere('dev.numeroVersion = :previousVersion')
            ->setParameter('numDevis', $numDevis)
            ->setParameter('previousVersion', $maxVersion - 1)  // Juste avant la version max
            ->getQuery()
            ->getResult();

        return $qb;
    }

    public function findNumeroVersionMax($numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->andWhere('dsv.statut <> :statut')
            ->setParameter('numDevis', $numDevis)
            ->setParameter('statut', 'erreur client interne')
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }

    public function findStatutDevis($numDit)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        try {
            $numeroVersionMax = $this->createQueryBuilder('dsv')
                ->select('MAX(dsv.numeroVersion)')
                ->where('dsv.numeroDit = :numDit')
                ->setParameter('numDit', $numDit)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return ''; // Retourner une chaîne vide si aucun numeroVersionMax n'est trouvé
        }

        if ($numeroVersionMax === null) {
            return ''; // Si le numeroVersionMax est null, retourner une chaîne vide
        }

        // Étape 2 : Utiliser le numeroVersionMax pour récupérer le statut
        try {
            $statut = $this->createQueryBuilder('dsv')
                ->select('dsv.statut')
                ->where('dsv.numeroDit = :numDit')
                ->andWhere('dsv.numeroVersion = :numeroVersionMax')
                ->setParameters([
                    'numeroVersionMax' => $numeroVersionMax,
                    'numDit' => $numDit,
                ])
                ->getQuery()
                ->getSingleScalarResult();

            return $statut;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return ''; // Retourner une chaîne vide si aucun statut n'est trouvé
        }
    }

}