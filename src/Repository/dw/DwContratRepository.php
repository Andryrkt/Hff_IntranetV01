<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwContratRepository extends EntityRepository
{
    /** 
     * Récupère le chemin du document associé à une référence de contrat.
     * @param string $refContrat La référence de contrat lequel on souhaite récupérer le chemin.
     */
    public function getPathByRefContrat(string $refContrat)
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path')
            ->where('d.reference = :refContrat')
            ->setParameter('refContrat', $refContrat)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
