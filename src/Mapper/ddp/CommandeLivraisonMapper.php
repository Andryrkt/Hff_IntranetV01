<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\CommandeLivraison;
use App\Entity\ddp\DemandePaiement;

class CommandeLivraisonMapper
{
    /**
     * Une ligne par commande (et par livraison quand elles sont connues : après arrivage)
     *
     * @param DemandePaiementDto|DdpDto $dto
     * @param DemandePaiement|null $demandePaiement
     * @return CommandeLivraison[]
     */
    public static function map($dto, ?DemandePaiement $demandePaiement = null): array
    {
        $commandesLivraisons = [];
        if (is_array($dto->numeroCommande) || is_array($dto->numeroFacture)) {
            $numeroFacture = is_array($dto->numeroFacture) ? implode(';', $dto->numeroFacture) : $dto->numeroFacture;
            foreach ((array) $dto->numeroCommande as $numeroCommande) {
                $livraisons = ($dto instanceof DdpDto ? $dto->livraisonsParCommande[$numeroCommande] ?? [] : []) ?: [$dto->numeroLivraison ?? null];
                foreach ($livraisons as $numeroLivraison) {
                    $commandesLivraisons[] = (new CommandeLivraison())
                        ->setNumeroCommande($numeroCommande)
                        ->setNumeroLivraison($numeroLivraison)
                        ->setNumeroFacture($numeroFacture)
                        // montant du commande à payer : uniquement saisi sur DdpDto (magasin)
                        ->setMontantCommandeAPayer($dto instanceof DdpDto ? $dto->montantCommande($numeroCommande) : null);
                }
            }
        } else {
            $commandesLivraisons[] = (new CommandeLivraison())
                ->setNumeroCommande($dto->numeroCommande)
                ->setNumeroLivraison($dto->numeroLivraison ?? null)
                ->setNumeroFacture($dto->numeroFacture);
        }

        if ($demandePaiement !== null) {
            foreach ($commandesLivraisons as $commandeLivraison) {
                $commandeLivraison->setDemandePaiement($demandePaiement);
            }
        }

        return $commandesLivraisons;
    }
}
