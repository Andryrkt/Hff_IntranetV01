<?php

namespace App\Dto\Magasin\Devis\Soumission;

class BcDto
{
    public ?string $numeroDevis;
    public ?string $numeroBc;
    public ?float $montantDevis = 0.0;
    public $montantBc;
    public ?int $numeroVersion;
    public ?string $statutBc;
    public ?string $observation = null;
    public ?string $utilisateur;
    public ?\DateTime $dateCreation;
    public ?\DateTime $dateModification;
    public ?\DateTime $dateBc;
    public ?string $codeSociete;

    public $pieceJoint01;
    public $pieceJoint2;
    public $lignes;

    public $userMail;
    public $codeClient;
    public $nomClient;
    public $modePayement;

    public $numeroVersionDevis;
}
