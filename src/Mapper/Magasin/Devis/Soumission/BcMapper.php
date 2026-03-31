<?php

namespace App\Mapper\Magasin\Devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\BcDto;

class BcMapper
{
    public static function toArrayBc(BcDto $dto)
    {

        return [
            'numero_devis' => $dto->numeroDevis,
            'numero_bc' => $dto->numeroBc,
            'montant_devis' => $dto->montantDevis,
            'montant_bc' => $dto->montantBc,
            'numero_version' => $dto->numeroVersion,
            'statut_bc' => $dto->statutBc,
            'observations' => $dto->observation,
            'utilisateur' => $dto->utilisateur,
            'date_creation' => $dto->dateCreation,
            'date_modification' => $dto->dateCreation,
            'date_bc' => $dto->dateBc,
            'code_societe' => $dto->codeSociete,
        ];
    }

    public static function toArrayUpdateDevis(BcDto $dto)
    {
        return [
            'statut_bc' => $dto->statutBc,
            'numero_devis' => $dto->numeroDevis,
            'date_bc' => $dto->dateBc,
            'date_modification' => $dto->dateCreation
        ];
    }
}
