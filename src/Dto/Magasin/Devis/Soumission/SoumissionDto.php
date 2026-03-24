<?php

namespace App\Dto\Magasin\Devis\Soumission;

class SoumissionDto
{
    // Soumission Devis
    public string $numeroDevis;
    public bool $validationPm = false;
    public ?string $tacheValidateur = null;
    public ?string $observation = null;
    public ?string $typeSoumission = null;
    public $constructeur;
    public ?string $codeSociete = null;
    public $pieceJoint01;
    public $pieceJoint2;
    public ?string $pieceJointExcel = null;
    public $remoteUrlCourt = null;
}
