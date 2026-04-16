<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiementCommande;
use App\Entity\ddp\DemandePaiement;

class DemandePaiementCommandeMapper
{
    public static function map(DemandePaiementDto $dto, ?DemandePaiement $demandePaiement = null): DemandePaiementCommande
    {
        $ddpCommande = new DemandePaiementCommande();
        $ddpCommande->setNumeroDdp($dto->numeroDdp)
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
            ->setClient($dto->appro ? 'appro' : 'magasin')
        ;

        if ($demandePaiement !== null) {
            $ddpCommande->setDemandePaiement($demandePaiement);
        }

        return $ddpCommande;
    }
}
