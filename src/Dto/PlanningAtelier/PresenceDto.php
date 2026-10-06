<?php

namespace App\Dto\PlanningAtelier;


class PresenceDto
{
    public bool $matin = false;

    public bool $apm = false;

    public ?float $heure = null;

    public ?float $hmtn = null;

    public ?float $hapm = null;

}
