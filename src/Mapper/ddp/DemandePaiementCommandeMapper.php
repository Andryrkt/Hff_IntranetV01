<?php

namespace App\Mapper\ddp;

use App\Entity\ddp\DemandePaiementCommande;

class DemandePaiementCommandeMapper
{
    public static function map($dto): DemandePaiementCommande
    {
        $ddpCommande = new DemandePaiementCommande();
        $ddpCommande->setNumeroDdp($dto->numeroDdp)
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
        ;

        return $ddpCommande;
    }
}
