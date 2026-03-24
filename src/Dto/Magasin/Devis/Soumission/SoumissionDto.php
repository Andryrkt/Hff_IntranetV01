<?php

namespace App\Dto\Magasin\Devis\Soumission;

class SoumissionDto
{
    // Soumission Devis
    public string $numeroDevis;
    public bool $estValidationPm = false;
    public ?string $tacheValidateur = null;
    public ?string $observation = null;
    public ?string $typeSoumission = null;
    public $constructeur;
    public ?string $codeSociete = null;
}
