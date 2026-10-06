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
     * @return DemandePaiementCommande
     */
    public static function map($dto, ?DemandePaiement $demandePaiement = null): DemandePaiementCommande
    {
        $ddpCommande = new DemandePaiementCommande();

        // verifie si le numero de commande est un tableau
        // si c'est le cas on itere sur le tableau pour enregistrer chaque numero de commande
        // si ce n'est pas un tableau on enregistre le numero de commande directement
        if (is_array($dto->numeroCommande)) {
            foreach ($dto->numeroCommande as $numeroCommande) {
                $ddpCommande
                    ->setNumeroCommande($numeroCommande)
                    ->setNumeroDdp($dto->numeroDdp)
                    ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
                    ->setClient($dto->appro ? 'appro' : 'magasin')
                ;
            }
        } else {
            $ddpCommande->setNumeroCommande($dto->numeroCommande)
                ->setNumeroDdp($dto->numeroDdp)
                ->setNumeroDemandeAppro($dto->numeroDemandeAppro ?? null)
                ->setClient($dto->appro ? 'appro' : 'magasin')
            ;
        }

        // l'objet demande paiement pour la liaison du table demande_paiement_commande avec le table demande_paiement
        if ($demandePaiement !== null) {
            $ddpCommande->setDemandePaiement($demandePaiement);
        }

        return $ddpCommande;
    }
}
