<?php

namespace App\Repository;


use Doctrine\ORM\EntityRepository;


class AgenceServiceAutoriserRepository extends EntityRepository
{

    // Ajoutez des méthodes personnalisées ici
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.Session_Utilisateur = ?')
            ->setParameter('?', $value)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}