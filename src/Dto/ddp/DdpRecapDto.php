<?php

namespace App\Dto\ddp;

class DdpRecapDto
{
    public ?string $dateCreation = null;
    public ?string $numeroDdp = null;
    public ?string $typeDemande = null;
    public ?string $numeroFacture = null;
    public ?string $numeroFactureIps = null;
    public float $montant = 0.00;
    public ?string $statut = null;
    public ?string $emetteur = null;
}
