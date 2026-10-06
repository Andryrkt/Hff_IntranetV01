<?php

namespace App\Dto\PlanningAtelier;

class PlanningAtelierDto
{
    public string $agenceEm = '';

    public string $section = '';

    public string $intitule = '';

    public string $itv = '';

    public string $numeroOr = '';

    public string $ressource = '';

    public float $nbJour = 0.0;

    public float $nbTotalJour = 0.0;

    public float $totalHeures = 0.0;

    /** @var array<string, PresenceDto> */
    public array $presences = [];

    public function getKey(): string
    {
        return sprintf('%s|%s|%s|%s|%s|%s',
            $this->agenceEm, $this->section, $this->intitule,
            $this->numeroOr, $this->itv, $this->ressource
        );
    }
}