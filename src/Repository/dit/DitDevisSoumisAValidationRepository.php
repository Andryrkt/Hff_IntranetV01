<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitDevisSoumisAValidationRepository extends EntityRepository
{
    public function findNumeroVersionMax($numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
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