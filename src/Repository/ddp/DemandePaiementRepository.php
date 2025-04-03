<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;

class DemandePaiementRepository extends EntityRepository
{
    public function CompteNbrligne($numerofournisseur)
    {
        $nbrLigne = $this->createQueryBuilder('ddp')
            ->select('COUNT(ddp.numeroFournisseur)')
            ->where('ddp.numeroFournisseur = :numFrn')
            ->andWhere('ddp.statut != :statut')
            ->setParameters([
                'numFrn' => $numerofournisseur,
                'statut' => 'Annulé'
                ])
            ->getQuery()
            ->getSingleScalarResult();
        ;

        return $nbrLigne ? $nbrLigne : 0;
    }

}