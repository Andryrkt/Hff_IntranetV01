<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Entity\ddp\DemandePaiementCommande;

class DemandePaiementCommandeMapper
{
    /**
     * @param DemandePaiementDto|DdpDto $dto
     * @param DemandePaiement|null $demandePaiement
     * @return DemandePaiementCommande[]
     */
    public static function map($dto, ?DemandePaiement $demandePaiement = null): array
    {
        // numeroCommande peut être un tableau (multi-select) ou une chaîne unique :
        // dans les deux cas on crée une entité par numéro de commande.
        $numeroCommandes = is_array($dto->numeroCommande) ? $dto->numeroCommande : [$dto->numeroCommande];

        $ddpCommandes = [];
        foreach ($numeroCommandes as $numeroCommande) {
            $ddpCommande = new DemandePaiementCommande();
            $ddpCommande
                ->setNumeroCommande($numeroCommande)
                ->setNumeroDdp($dto->numeroDdp)
                ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
                ->setClient($dto->appro ? 'appro' : 'magasin')
            ;

            // l'objet demande paiement pour la liaison du table demande_paiement_commande avec le table demande_paiement
            if ($demandePaiement !== null) {
                $ddpCommande->setDemandePaiement($demandePaiement);
            }

            $ddpCommandes[] = $ddpCommande;
        }

        return $ddpCommandes;
    }
}
