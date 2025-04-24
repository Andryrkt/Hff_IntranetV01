<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;

class HistoriqueStatutDdpRepository extends EntityRepository
{
    public function getHistoriqueStatut($numeroDdp)
    {
        return $this->createQueryBuilder('h')
            ->where('h.numeroDdp = :numeroDdp')
            ->setParameter('numeroDdp', $numeroDdp)
            ->orderBy('h.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}