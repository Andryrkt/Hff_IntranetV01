<?php

namespace App\Model\magasin\bc;

use Symfony\Component\HttpFoundation\File\File;

class BcMagasinDto
{
    public ?string $numeroDevis = null;
    public ?string $numeroBc = null;
    public ?string $montantBc = null;
    public ?string $observation = null;
    public ?File $pieceJoint01 = null;
    public array $pieceJoint2 = [];
    public string $codeClient;
    public string $nomClient;
    public string $modePayement;

    /** @var BcMagasinLigneDto[] */
    public array $lignes = [];
}
