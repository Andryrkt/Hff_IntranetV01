<?php

namespace App\Dto\Magasin\Devis;

class DevisNegDto
{
    public ?string $statutDw = '';
    public ?string $statutBc = '';
    public string $numeroDevis;
    public string $dateCreation;
    public ?string $emmeteur = '';
    public ?string $client = '';
    public ?string $referenceClient = '';
    public ?float $montantDevis = 0.00;
    public $dateEnvoiDevisAuClient = null;
    public ?string $positionIps = '';
    public ?string $utilisateurCreateurDevis = '';
    public ?string $soumisPar = '';
    public string $devise = '';
    public  $constructeur;

    public ?int $numeroVersion = 0;
    public int $nombreLignes = 0;
    public string $typeSoumission = '';
    public $dateMajStatut;
    public string $utilisateur = '';
    public bool $cat = false;
    public bool $nonCat = false;
    public string $nomFichier = '';
    public int $sommeNumeroLignes;
    public $datePointage = null;
    public ?string $tacheValidateur = null;
    public $estValidationPm = false;
    public ?string $relance = '';
    public $dateBc = null;
    public ?string $observation = null;
    public $pieceJoint01;
    public $pieceJoint2;
    public ?string $pieceJointExcel = null;
    public ?bool $migration = false;
    public ?string $statutTemp = '';
    public ?string $statutRelance = null;
    public $stopProgressionGlobal;
    public $dateStopGlobal;
    public $motifStopGlobal;
    public $dateRepriseManuel;
}
