<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\CommandeLivraison;
use App\Entity\ddp\DemandePaiement;

class CommandeLivraisonMapper
{
    public static function map(DemandePaiementDto $dto, ?DemandePaiement $demandePaiement = null): CommandeLivraison
    {
        $commandeLivraison = new CommandeLivraison();
        $commandeLivraison
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroLivraison($dto->numeroLivraison ?? null)
            ->setNumeroFacture(is_array($dto->numeroFacture) ? implode('', $dto->numeroFacture) : $dto->numeroFacture)
        ;

        if ($demandePaiement !== null) {
            $commandeLivraison->setDemandePaiement($demandePaiement);
        }

        return $commandeLivraison;
    }
}
