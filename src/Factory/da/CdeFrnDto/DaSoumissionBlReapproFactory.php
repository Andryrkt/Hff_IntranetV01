<?php

namespace App\Factory\da\CdeFrnDto;

use App\Dto\Da\ListeCdeFrn\DaSoumisionBlReapproDto;

class DaSoumissionBlReapproFactory
{


    public static function createFromDto(string  $numCde, string $numOr, string $numDa): DaSoumisionBlReapproDto
    {
        $dto = new DaSoumisionBlReapproDto();
        $dto->numCde = $numCde;
        $dto->numOr = $numOr;
        $dto->numDa = $numDa;
        $dto->estFactureReappro = true;

        return $dto;
    }
}
