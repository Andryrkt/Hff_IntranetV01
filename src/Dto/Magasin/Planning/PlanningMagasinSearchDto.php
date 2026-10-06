<?php

namespace App\Dto\Magasin\Planning;

class PlanningMagasinSearchDto
{
    public ?string $fournisseur = null;

    public ?string $numeroCommande = null;

    /** @var array{agence: ?\App\Entity\admin\Agence, service: ?\App\Entity\admin\Service} */
    public ?array $agenceService = [];

    /** @var array{debut: ?\DateTimeInterface, fin: ?\DateTimeInterface} */
    public ?array $dateCommande = [];

    public ?int $months = 3;
}
