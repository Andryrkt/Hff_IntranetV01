<?php

namespace App\Dto\Magasin\Devis;

class PointageRelanceDto
{
    public ?string $numeroDevis = null;
    public ?int $numeroVersion = null;
    public ?\DateTimeInterface $dateDeRelance = null;
    public ?string $utilisateur = null;
    public ?\DateTimeInterface $dateDePointage = null;
    public ?string $agence = null;
    public ?int $numeroRelance = null;
}
