<?php

namespace App\Mapper\Magasin\Devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\SoumissionDto;

class SoumissionMapper
{
    public static function toDto(array $data): SoumissionDto
    {
        // TODO: à rectifier il faut envoyer un parmamettre pour savoir le type de soumission 
        $dto = new SoumissionDto();
        $dto->numeroDevis = $data['numeroDevis'];
        $dto->estValidationPm = $data['estValidationPm'];
        $dto->tacheValidateur = $data['tacheValidateur'];
        $dto->observation = $data['observation'];
        $dto->typeSoumission = $data['typeSoumission'];
        $dto->constructeur = $data['constructeur'];
        $dto->codeSociete = $data['codeSociete'];
        return $dto;
    }
}
