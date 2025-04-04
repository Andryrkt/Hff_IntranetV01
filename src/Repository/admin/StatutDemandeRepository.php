<?php

namespace App\Repository\admin;



use Doctrine\ORM\EntityRepository;


class StatutDemandeRepository extends EntityRepository
{

    // Ajoutez des méthodes personnalisées ici
    public function findByCodeStatut($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.codeStatut = :val1')
            ->andWhere('m.codeApp = :val2')
            ->setParameter('val1', $value)
            ->setParameter('val2', 'TKI')
            ->getQuery()
            ->getOneOrNullResult() // Retourne un seul résultat ou null
        ;
    }
}
