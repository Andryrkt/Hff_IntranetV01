<?php

namespace App\Mapper\ddp;

use App\Entity\ddp\DemandePaiementCommande;

class DemandePaiementCommandeMapper
{
    public static function map($dto): DemandePaiementCommande
    {
        $ddpCommande = new DemandePaiementCommande();
        $ddpCommande->setNumeroDdp($dto->numeroDdp)
            ->setNumeroCommande($dto->numeroCommande)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
        ;

        return $ddpCommande;
    }
}
