<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\CommandeLivraison;

class CommandeLivraisonMapper
{
    public static function map(DemandePaiementDto $dto): CommandeLivraison
    {
        $commandeLivraison = new CommandeLivraison();
        $commandeLivraison
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroLivraison($dto->numeroLivraison ?? null)
            ->setNumeroFacture(is_array($dto->numeroFacture) ? implode('', $dto->numeroFacture) : $dto->numeroFacture)
        ;

        return $commandeLivraison;
    }
}
