<?php

namespace App\Factory\da\CdeFrnDto;

use App\Dto\Da\ListeCdeFrn\DaSoumissionBcDto;

class DaSoumissionBcFactory
{
    public static function init(string $numeroCde, string $numDa, int $numOr): DaSoumissionBcDto
    {
        $dto = new DaSoumissionBcDto();
        $dto->numeroCde = $numeroCde;
        $dto->numeroDemandeAppro = $numDa;
        $dto->numeroOR = $numOr;

        return $dto;
    }
}
