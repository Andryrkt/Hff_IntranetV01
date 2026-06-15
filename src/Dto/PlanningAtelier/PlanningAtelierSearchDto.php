<?php

namespace App\Dto\PlanningAtelier;

class PlanningAtelierSearchDto
{
    public ?int $numeroSemaine = null;

    public ?\DateTimeInterface $dateDebut = null;

    public ?\DateTimeInterface $dateFin = null;

    public ?string $numeroOr = null;

    public ?string $ressource = null;

    public ?string $section = null;

    public ?string $agenceDeb = null;

    public ?string $agenceEm = null;

    public ?array $serviceDeb = [];
}