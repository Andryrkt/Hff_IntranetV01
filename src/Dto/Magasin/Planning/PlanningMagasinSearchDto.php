<?php

namespace App\Dto\Magasin\Planning;

class PlanningMagasinSearchDto
{
    public ?string $fournisseur = null;

    public ?string $numeroCommande = null;

    public ?int $months = 3;
}
