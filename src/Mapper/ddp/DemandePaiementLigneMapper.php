<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiementLigne;

class DemandePaiementLigneMapper
{
    public static function map(DemandePaiementDto $dto): array
    {
        $lignes = [];

        if (is_array($dto->numeroCommande)) {
            $nb = count($dto->numeroCommande);
        } else {
            $nb = 1;
        }

        for ($i = 0; $i < $nb; $i++) {
            $ligne = new DemandePaiementLigne();
            $ligne->setNumeroDdp($dto->numeroDdp)
                ->setNumeroLigne($i + 1)
                ->setNumeroCommande($dto->numeroCommande[$i] ?? '')
                ->setNumeroFacture(self::numeroFacture($dto, $i))
                ->setMontantFacture($dto->montantAPayer())
                ->setNumeroVersion(1)
                ->setRatioMontantPayer($dto->ratioMontantpayer());

            $lignes[] = $ligne;
        }

        return $lignes;
    }

    private static function numeroFacture(DemandePaiementDto $dto, int $i): string
    {
        return is_array($dto->numeroFacture) && array_key_exists($i, $dto->numeroFacture)
            ? $dto->numeroFacture[$i]
            : '-';
    }
}
