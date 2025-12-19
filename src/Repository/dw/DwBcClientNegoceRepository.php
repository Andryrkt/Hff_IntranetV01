<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwBcClientNegoceRepository extends EntityRepository
{
    /**
     * Récupère le dernier BCC négocié validé pour un numéro de devis donné.
     *
     * @param string $numeroDevis
     *
     * @return array{numeroBccNeg:string,path:string}|null
     */
    public function findLastValidatedBcc(string $numeroDevis): ?array
    {
        return $this->createQueryBuilder('d')
            ->select('d.numeroBccNeg', 'd.path')
            ->where('d.numeroDevis = :numeroDevis')
            ->andWhere('d.statutBccNeg = :statut')
            ->setParameter('numeroDevis', $numeroDevis)
            ->setParameter('statut', 'Validé')
            ->orderBy('d.idBccNeg', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
