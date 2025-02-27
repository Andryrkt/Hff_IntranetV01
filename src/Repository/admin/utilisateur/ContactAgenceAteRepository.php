<?php

namespace App\Repository\admin\utilisateur;


use Doctrine\ORM\EntityRepository;


class ContactAgenceAteRepository extends EntityRepository
{

    public function findContactSelonAtelier(?string $atelier)
    {
        $qb = $this->createQueryBuilder('ca');

        if ($atelier === null) {
            $qb->where('ca.atelier IS NULL');
        } else {
            $qb->where('ca.atelier = :atelier')
            ->setParameter('atelier', $atelier);
        }

        return $qb->getQuery()->getResult();
    }

}