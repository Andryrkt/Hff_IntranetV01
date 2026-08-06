<?php

namespace App\Dto\Magasin\Planning;

class PlanningMagasinSearchDto
{
    public ?string $nomFournisseur = null;

    public ?string $codeFournisseur = null;

    public ?string $numeroCommande = null;

    public ?int $months = 3;
}
