<?php

namespace App\Mapper\ddp;

use App\Entity\ddp\CommandeLivraison;

class CommandeLivraisonMapper
{
    public static function map($dto): CommandeLivraison
    {
        $commandeLivraison = new CommandeLivraison();
        $commandeLivraison
            ->setNumeroCommande(is_array($dto->numeroCommande) ? implode('', $dto->numeroCommande) : $dto->numeroCommande)
            ->setNumeroLivraison($dto->numLiv ?? null)
            ->setNumeroFacture(is_array($dto->numeroFacture) ? implode('', $dto->numeroFacture) : $dto->numeroFacture)

        ;

        return $commandeLivraison;
    }
}
