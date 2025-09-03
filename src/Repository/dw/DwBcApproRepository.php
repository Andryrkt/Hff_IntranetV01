<?php

namespace App\Repository\dw;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DwBcApproRepository extends EntityRepository
{
    /** 
     * Récupère le chemin du document associé à un numéro de bon de commande (BC).
     * @param string $numeroCde Le numéro de bon de commande pour lequel on souhaite récupérer le chemin.
     * @return string|null Le chemin du document associé au numéro de bon de commande, ou
     */
    public function getPath(string $numeroCde): ?string
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path')
            ->where('d.numeroBc = :numeroBc')
            ->setParameter('numeroBc', $numeroCde)
            ->orderBy('d.path', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /** 
     * Récupère le chemin du document associé à un numéro de demande Appro.
     * @param string $numeroDa Le numéro de demande appro lequel on souhaite récupérer le chemin.
     */
    public function getPathAndNumeroBCByNumDa(string $numeroDa)
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path', 'd.numeroBc')
            ->where('d.numeroDa = :numeroDa')
            ->setParameter('numeroDa', $numeroDa)
            ->orderBy('d.numeroBc', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
