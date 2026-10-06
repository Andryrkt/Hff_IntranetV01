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
            $ddpRecapDto->montant = $dto->montantAPayer;
            $ddpRecapDto->statut = $dto->statut;
            $ddpRecapDto->emetteur = $dto->demandeur;
            $ddpRecapDto->ratio = (float) number_format(($dto->montantAPayer / $totalMontantCommande) * 100, 2);
            $ddpRecapDtoList[] = $ddpRecapDto;
        }

        return $ddpRecapDtoList;
    }
}
