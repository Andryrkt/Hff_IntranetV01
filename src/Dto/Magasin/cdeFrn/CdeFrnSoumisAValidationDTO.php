<?php

namespace App\Dto\Magasin\cdeFrn;

class CdeFrnSoumisAValidationDTO
{
    public ?string    $numCde              = null;
    public ?string    $codeFrn             = null;
    public ?string    $libelleFrn          = null;
    public ?int       $numVersion          = null;
    public ?\DateTime $dateHeureSoumission = null;
    public ?string    $statut              = null;

    public function __construct()
    {
        $this->dateHeureSoumission = new \DateTime();
        $this->statut              = 'Soumis à validation';
    }
}
