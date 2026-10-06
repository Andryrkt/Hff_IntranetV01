<?php

namespace App\Mapper\PlanningAtelier;

use App\Dto\PlanningAtelier\PlanningAtelierDto;

class PlanningAtelierMapper
{

    public function mapToDto(array $item): PlanningAtelierDto
    {
        $dto = new PlanningAtelierDto();

        $dto->agenceEm = $item['agence_em'] ?? '';
        $dto->section = $item['section'] ?? '';
        $dto->intitule = $item['intitule'] ?? '';
        $dto->numeroOr = $item['num_or'] ?? '';
        $dto->itv = $item['itv'] ?? '';
        $dto->ressource = $item['ressource'] ?? '';
        $dto->nbJour = (float) ($item['nb_jour'] ?? 0);

        return $dto;
    }

}