<?php

namespace App\Model\magasin\bc;

class BcMagasinLigneDto
{
    public ?string $numeroLigne = null;
    public ?string $constructeur = null;
    public ?string $ref = null;
    public ?string $designation = null;
    public ?string $qte = null;
    public ?string $prixHt = null;
    public ?string $montantNet = null;
    public ?string $remise1 = null;
    public ?string $remise2 = null;

    public bool $ras = true;
    public bool $qteModifier = false;
    public bool $supprimer = false;

    public ?string $nouvelleQte = null;
}
