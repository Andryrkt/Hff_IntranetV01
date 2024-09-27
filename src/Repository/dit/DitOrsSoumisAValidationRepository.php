<?php

namespace App\Repository\dit;

use Doctrine\ORM\EntityRepository;

class DitOrsSoumisAValidationRepository extends EntityRepository
{
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
        $qb = $this->createQueryBuilder('osv');

        // Sous-requête pour obtenir la version maximale
        $subqueryMax = $this->createQueryBuilder('osv2')
            ->select('MAX(osv2.numeroVersion)')
            ->where('osv2.numeroOR = :numOr')
            ->getDQL();

        // Requête principale pour obtenir la version juste avant le MAX
        $orSoumisAvant = $qb
            ->where('osv.numeroOR = :numOr')
            ->setParameter('numOr', $numOr)
            ->andWhere($qb->expr()->lt('osv.numeroVersion', '(' . $subqueryMax . ')'))  // Moins que la version MAX
            ->orderBy('osv.numeroVersion', 'DESC')  // Trier par version décroissante
            ->setMaxResults(1)  // Obtenir seulement la version immédiatement avant
            ->getQuery()
            ->getOneOrNullResult();  // Récupérer un seul résultat ou null

        return $orSoumisAvant;
    }


}