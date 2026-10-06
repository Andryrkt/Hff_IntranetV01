<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\CommandeLivraison;
use App\Entity\ddp\DemandePaiement;

class CommandeLivraisonMapper
{
    /**
     * @param DemandePaiementDto|DdpDto $dto
     * @param DemandePaiement|null $demandePaiement
     * @return CommandeLivraison
     */
    public static function map($dto, ?DemandePaiement $demandePaiement = null): CommandeLivraison
    {
        $commandeLivraison = new CommandeLivraison();
        if (is_array($dto->numeroCommande) || is_array($dto->numeroFacture)) {
            foreach ($dto->numeroCommande as $numeroCommande) {
                $commandeLivraison
                    ->setNumeroCommande($numeroCommande)
                    ->setNumeroLivraison($dto->numeroLivraison ?? null)
                    ->setNumeroFacture($dto->numeroFacture)
                ;
            }
        } else {
            $commandeLivraison
                ->setNumeroCommande($dto->numeroCommande)
                ->setNumeroLivraison($dto->numeroLivraison ?? null)
                ->setNumeroFacture($dto->numeroFacture)
            ;
        }

        if ($demandePaiement !== null) {
            $commandeLivraison->setDemandePaiement($demandePaiement);
        }

        return $commandeLivraison;
    }
}
