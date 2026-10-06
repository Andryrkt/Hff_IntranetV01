<?php

namespace App\Mapper\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Entity\ddp\DemandePaiementLigne;

class DdpLigneMapper
{
    /**
     * @param DdpDto $dto
     */
    public static function map(DdpDto $dto): array
    {
        $lignes = [];
        $nb = is_array($dto->numeroCommande) ? count($dto->numeroCommande) : 1;

        for ($i = 0; $i < $nb; $i++) {
            $ligne = new DemandePaiementLigne();
            $ligne->setNumeroDdp($dto->numeroDdp)
                ->setNumeroLigne($i + 1)
                ->setNumeroCommande($dto->numeroCommande[$i] ?? '')
                ->setNumeroFacture(self::numeroFacture($dto, $i))
                ->setMontantFacture($dto->montantAPayer())
                ->setNumeroVersion(1)
                ->setRatioMontantPayer(0); // TODO: montant total à rechercher pour le magasin

            $lignes[] = $ligne;
        }

        return $lignes;
    }

    /**
     * @param DdpDto $dto
     */
    private static function numeroFacture(DdpDto $dto, int $i): string
    {
        return is_array($dto->numeroFacture) && array_key_exists($i, $dto->numeroFacture)
            ? $dto->numeroFacture[$i]
            : '-';
    }
}
