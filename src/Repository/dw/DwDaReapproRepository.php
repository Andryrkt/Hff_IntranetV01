<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwDaReapproRepository extends EntityRepository
{
    /** 
     * Récupère le chemin du document associé à un numéro de demande Appro.
     * @param string $numeroDa Le numéro de demande appro lequel on souhaite récupérer le chemin.
     */
    public function getPathByNumDa(string $numeroDa)
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path', 'd.numeroDaReappro', 'd.numeroVersion')
            ->where('d.numeroDaReappro = :numeroDa')
            ->setParameter('numeroDa', $numeroDa)
            ->orderBy('d.numeroVersion', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
