<?php

namespace App\Repository\admin\badm;



use Doctrine\ORM\EntityRepository;


class TypeMouvementRepository extends EntityRepository
{

    // Ajoutez des méthodes personnalisées ici
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}