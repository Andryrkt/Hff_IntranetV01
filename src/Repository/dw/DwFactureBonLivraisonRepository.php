<?php

namespace App\Repository\dw;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DwFactureBonLivraisonRepository extends EntityRepository
{
    /** 
     * Récupère le chemin du document associé à un numéro de demande Appro.
     * @param string $numeroDa Le numéro de demande appro lequel on souhaite récupérer le chemin.
     * @return string|null Le chemin du document associé au numéro de demande appro
     */
    public function getPathByNumDa(string $numeroDa): ?string
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path')
            ->where('d.numeroDa = :numeroDa')
            ->setParameter('numeroDa', $numeroDa)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }
}
