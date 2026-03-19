<?php

namespace App\Dto\Magasin\Devis;

class DevisSearchDto
{
    public ?string $numeroDevis = null;
    public ?string $codeClient = null;
    public ?string $Operateur = null;
    public ?string $statutDw = null; // statut devis
    public ?string $statutIps = null; // position IPS
    public ?array $emetteur = [];
    public ?array $dateCreation = [];
    public ?string $statutBc = null;
    public ?string $creePar = null;
    public ?string $numeroPO = null;
    public ?string $filterRelance = null;
}
