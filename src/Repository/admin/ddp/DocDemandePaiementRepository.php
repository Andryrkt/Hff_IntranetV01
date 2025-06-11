<?php

namespace App\Repository\admin\ddp;

use Doctrine\ORM\EntityRepository;

class DocDemandePaiementRepository extends EntityRepository
{
    public function getFileName(string $numDdp)
    {
        return $this->createQueryBuilder('d')
            ->select('d.nomFichier, d.nomDossier')
            ->where('d.numeroDdp = :numDdp')
            ->andWhere('d.nomDossier IS NOT NULL')
            ->setParameter('numDdp', $numDdp)
            ->getQuery()
            ->getArrayResult();  // Retourne les résultats sous forme de tableau associatif
    }
    



}