<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DdpRecapDto;

class DdpRecapMapper
{
    public static function map(array $demandePaiemenetDto, float $totalMontantCommande): array
    {
        $ddpRecapDtoList = [];
        foreach ($demandePaiemenetDto as $dto) {
            $ddpRecapDto = new DdpRecapDto();
            $ddpRecapDto->dateCreation = $dto->dateDemande->format('d/m/Y');
            $ddpRecapDto->numeroDdp = $dto->numeroDdp;
            $ddpRecapDto->typeDemande = $dto->typeDemande->getLibelle();
            $ddpRecapDto->numeroFacture = $dto->numeroFacture;
            $ddpRecapDto->numeroFactureIps = $dto->numeroFactureIps;
            // montantAPayer peut être brut ('1238500') ou au format FR ('1.295.300,00')
            $ddpRecapDto->montant = strpos($dto->montantAPayer, ',') !== false
                ? (float) str_replace(['.', ','], ['', '.'], $dto->montantAPayer)
                : (float) $dto->montantAPayer;
            $ddpRecapDto->statut = $dto->statut;
            $ddpRecapDto->emetteur = $dto->demandeur;
            $ddpRecapDto->ratio = $totalMontantCommande > 0 ? round($ddpRecapDto->montant / $totalMontantCommande * 100, 2) : 0;
            $ddpRecapDtoList[] = $ddpRecapDto;
        }

        return $ddpRecapDtoList;
    }
}
