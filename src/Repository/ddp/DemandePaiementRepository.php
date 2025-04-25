<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;
use App\Service\TableauEnStringService;

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
            ->getSingleScalarResult();;

        return $nbrLigne ? $nbrLigne : 0;
    }

    public function recuperation_numFrs_numCde($numeroDdp)
    {
        $data = $this->createQueryBuilder('ddp')
            ->select('ddp.numeroFournisseur, ddp.numeroCommande')
            ->where('ddp.numeroDdp = :numDdp')
            ->setParameters([
                'numDdp' => $numeroDdp
            ])
            ->getQuery()
            ->getOneOrNullResult();

            if ($data) {
        return [
            'numeroFournisseur' => $data['numeroFournisseur'],
            'numeroCommande' => is_array($data['numeroCommande']) 
                ? TableauEnStringService::TableauEnString(",", $data['numeroCommande']) 
                : $data['numeroCommande']
        ];
    }

    return null;
            return $data;
    }
}
