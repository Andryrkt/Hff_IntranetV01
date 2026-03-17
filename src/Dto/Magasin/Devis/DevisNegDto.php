<?php

namespace App\Dto\Magasin\Devis;

class DevisNegDto
{
    public string $numeroDevis;
    public ?int $numeroVersion = 0;
    public ?string $statutDw = '';
    public int $nombreLignes = 0;
    public float $montantDevis = 0.00;
    public string $devise = '';
    public string $typeSoumission = '';
    public $dateMajStatut;
    public string $utilisateur = '';
    public bool $cat = false;
    public bool $nonCat = false;
    public string $nomFichier = '';
    public $dateEnvoiDevisAuClient = null;
    public int $sommeNumeroLignes;
    public $datePointage = null;
    public ?string $tacheValidateur = null;
    public $estValidationPm = false;
    public ?string $statutBc = '';
    public ?string $relance = '';
    public $dateBc = null;
    public ?string $observation = null;
    public $pieceJoint01;
    public $pieceJoint2;
    public  $constructeur;
    public ?string $pieceJointExcel = null;
    public ?bool $migration = false;
    public ?string $statutTemp = '';
    public ?string $statutRelance = null;
    public $stopProgressionGlobal;
    public $dateStopGlobal;
    public $motifStopGlobal;
    public $dateRepriseManuel;
}
