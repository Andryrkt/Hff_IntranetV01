<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiementCommande;

class DemandePaiementCommandeMapper
{
    public static function map(DemandePaiementDto $dto): DemandePaiementCommande
    {
        $ddpCommande = new DemandePaiementCommande();
        $ddpCommande->setNumeroDdp($dto->numeroDdp)
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
        ;

        return $ddpCommande;
    }
}
