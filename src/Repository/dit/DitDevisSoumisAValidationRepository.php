<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitDevisSoumisAValidationRepository extends EntityRepository
{


    public function findDernierStatutDevis($numDevis)
    {
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
            ->setMaxResults(1) // Ajout d'une limite pour garantir un seul résultat
            ->getQuery()
            ->getOneOrNullResult();

        return $dernierStatut ? $dernierStatut['statut'] : null;
    }

    public function findDevisSoumiAvant($numDevis)
    {
        $qb = $this->createQueryBuilder('dev');

        $subquery = $this->createQueryBuilder('dev2')
            ->select('MAX(dev2.numeroVersion)')
            ->where('dev2.numeroDevis = :numDevis')
            ->getDQL();

        $orSoumisAvant = $qb
            ->where('dev.numeroDevis = :numDevis')
            ->andWhere('dev.montantItv <> :mttItv')
            ->setParameter('numDevis', $numDevis)
            ->setParameter('mttItv', 0.00)
            ->andWhere($qb->expr()->eq('dev.numeroVersion', '(' . $subquery . ')'))
            ->getQuery()
            ->getResult();

        return $orSoumisAvant;
    }


    public function findDevisSoumiAvantMax($numDevis)
    {
        // Étape 1: Récupérer la version maximale pour le numeroOR donné
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
            ->andWhere('dev.montantItv <> :mttItv')
            ->andWhere('dev.numeroVersion = :previousVersion')
            ->setParameter('mttItv', 0.00)
            ->setParameter('numDevis', $numDevis)
            ->setParameter('previousVersion', $maxVersion - 1)  // Juste avant la version max
            ->getQuery()
            ->getResult();

        return $qb;
    }

    public function findDevisSoumiAvantForfait($numDevis)
    {
        $qb = $this->createQueryBuilder('dev');

        $subquery = $this->createQueryBuilder('dev2')
            ->select('MAX(dev2.numeroVersion)')
            ->where('dev2.numeroDevis = :numDevis')
            ->getDQL();

        $orSoumisAvant = $qb
            ->where('dev.numeroDevis = :numDevis')
            ->andWhere('dev.montantForfait IS NOT NULL')
            ->setParameter('numDevis', $numDevis)
            ->andWhere($qb->expr()->eq('dev.numeroVersion', '(' . $subquery . ')'))
            ->getQuery()
            ->getResult();

        return $orSoumisAvant;
    }


    public function findDevisSoumiAvantMaxForfait($numDevis)
    {
        // Étape 1: Récupérer la version maximale pour le numeroOR donné
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
            ->andWhere('dev.montantForfait IS NOT NULL')
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
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();

            return $statut;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return ''; // Retourner une chaîne vide si aucun statut n'est trouvé
        }
    }

    /**
     * Methode qui recupère tous les information du dernière devis soumis 
     *
     * @param string $numDit
     * @return void
     */
    public function findInfoDevis(string $numDit)
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
            $devis = $this->createQueryBuilder('dsv')
                ->where('dsv.numeroDit = :numDit')
                ->andWhere('dsv.numeroVersion = :numeroVersionMax')
                ->andWhere('dsv.statut = :statut')
                ->setParameters([
                    'numeroVersionMax' => $numeroVersionMax,
                    'numDit' => $numDit,
                    'statut' => 'Validé atelier'
                ])
                ->getQuery()
                ->getResult();

            return $devis;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return ''; // Retourner une chaîne vide si aucun statut n'est trouvé
        }
    }

    public function findDevisVpValide($numDevis)
    {
        // Récupérer le numéro de version maximal pour le devis donné
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        // Si aucun numéro de version trouvé, retourner 0
        if ($numeroVersionMax === null) {
            return 0;
        }

        // Compter le nombre de devis validés pour la version maximale
        return $this->createQueryBuilder('dsv')
            ->select('COUNT(dsv.id)') // Assurez-vous que 'id' est une clé unique dans votre entité
            ->Where('dsv.numeroDevis = :numDevis')
            ->andWhere('dsv.numeroVersion = :numVersion')
            ->andWhere('dsv.statut Like :statut')
            ->setParameters([
                'numVersion' => $numeroVersionMax,
                'statut' => '%Validé%',
                'numDevis' => $numDevis
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findStatut($numDevis)
    {
        // Récupérer le numéro de version maximal pour le devis donné
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        // Si aucun numéro de version trouvé, retourner 0
        if ($numeroVersionMax === null) {
            return 0;
        }

        return $this->createQueryBuilder('dsv')
            ->select('dsv.statut')
            ->where('dsv.numeroDevis = :numDevis')
            ->andWhere('dsv.numeroVersion = :numVersion')
            ->setParameters([
                'numVersion' => $numeroVersionMax,
                'numDevis' => $numDevis
            ])
            ->getQuery()
            ->getSingleColumnResult();;
    }

    public function findNbrPieceMagasin($numDevis)
    {
        // Récupérer le numéro de version maximal pour le devis donné
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        // Si aucun numéro de version trouvé, retourner 0
        if ($numeroVersionMax === null) {
            return 0;
        }

        return $this->createQueryBuilder('dsv')
            ->select('DISTINCT dsv.nombreLignePiece')
            ->where('dsv.numeroDevis = :numDevis')
            ->andWhere('dsv.numeroVersion = :numVersion')
            ->setParameters([
                'numVersion' => $numeroVersionMax,
                'numDevis' => $numDevis
            ])
            ->getQuery()
            ->getSingleScalarResult();;
    }

    public function findVerificationPrimeSoumission($numDevis)
    {
        // Récupérer le numéro de version maximal pour le devis donné
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('COUNT(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        // Si aucun numéro de version trouvé, retourner 0
        if ($numeroVersionMax === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function findMontantItv(string $numDevis)
{
    // Récupérer le numéro de version maximal pour le devis donné
    $numeroVersionMax = $this->createQueryBuilder('dsv')
        ->select('MAX(dsv.numeroVersion)')
        ->where('dsv.numeroDevis = :numDevis')
        ->setParameter('numDevis', $numDevis)
        ->getQuery()
        ->getSingleScalarResult();

    // Si aucun numéro de version trouvé, retourner 0
    if ($numeroVersionMax === null) {
        return 0;
    }

    // Calculer la somme du montantItv pour la version maximale
    $sommeMontantItv = $this->createQueryBuilder('dsv')
        ->select('SUM(dsv.montantItv)')
        ->where('dsv.numeroDevis = :numDevis')
        ->andWhere('dsv.numeroVersion = :numVersion')
        ->setParameters([
            'numVersion' => $numeroVersionMax,
            'numDevis' => $numDevis
        ])
        ->getQuery()
        ->getSingleScalarResult();

    return $sommeMontantItv ?? 0;
}

}
