<?php

namespace App\Mapper\ddp;

use App\Entity\ddp\CommandeLivraison;

class CommandeLivraisonMapper
{
    public static function map($dto): CommandeLivraison
    {
        $commandeLivraison = new CommandeLivraison();
        $commandeLivraison
            ->setNumeroCommande($dto->numeroCommande)
            ->setNumeroLivraison($dto->numeroLivraison ?? null)
            ->setNumeroFacture($dto->numeroFacture ?? null)

        ;

        return $commandeLivraison;
    }
}
