<?php

namespace App\Factory\da\CdeFrnDto;

use App\Dto\Da\ListeCdeFrn\DaSoumisionBlReapproDto;

class DaSoumissionBlReapproFactory
{   
    

    public static function createFromDto(string  $numCde): DaSoumisionBlReapproDto
    {
        $dto = new DaSoumisionBlReapproDto();
        $dto->numCde = $numCde;
        $dto->estFactureReappro = true;

        return $dto;
    }
}
