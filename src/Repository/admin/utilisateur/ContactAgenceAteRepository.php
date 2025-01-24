<?php

namespace App\Repository\admin\utilisateur;


use Doctrine\ORM\EntityRepository;


class ContactAgenceAteRepository extends EntityRepository
{

    public function findContactSelonAtelier(string $atelier)
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.atelier = :atelier')
            ->setParameter('atelier', $atelier)
            ->getQuery()
            ->getResult()
        ;
    }
}