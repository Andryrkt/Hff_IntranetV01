<?php

namespace App\Mapper\Magasin\Devis\Pointage;

use App\Dto\Magasin\Devis\Pointage\EnvoyerAuClientDto;

class PointageMapper
{
    public static function toArrayEnvoyerAuClient(EnvoyerAuClientDto $dto)
    {
        // TODO: encore à ameliorer
        return [
            'statut_dw' => $dto->statutDw,
            'statut_bc' => $dto->statutBc,
            'code_societe' => $dto->codeSociete,
            'date_modification' => $dto->datePointage,
            'date_pointage' => $dto->datePointage,
            'date_envoye_devis_client' => $dto->dateEnvoiDevisAuClient
        ];
    }
}
