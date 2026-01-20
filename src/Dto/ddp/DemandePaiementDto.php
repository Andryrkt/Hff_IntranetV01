<?php

namespace App\Dto\ddp;

use App\Entity\admin\ddp\TypeDemande;

class DemandePaiementDto
{
    public string $numeroDdp;
    public ?TypeDemande $typeDemande = null;
    public $numeroFournisseur;
    public $ribFournisseur;
    public $beneficiaire; // nom du fournisseur
    public $motif;
    public $agenceDebiter;
    public $serviceDebiteur;
    public $statut;
    public $adresseMailDemandeur;
    public $demandeur;
    public $modePaiement;
    public float $montantAPayers = 0.00;
    public ?string $contact;
    public array $numeroCommande = [];
    public array $numeroFacture = [];
    public ?string $devise = null;
    public ?string $statutDossierRegul;
    public int $numeroVersion = 0;
    public bool $estAutresDoc = false;
    public ?string $nomAutreDoc = null;
    public bool $estCdeClientExterneDoc = false;
    public array $nomCdeClientExterneDoc = [];
    public array $numeroDossierDouane = [];
    public bool $appro = false;
    public string $montantAPayer = '0';

    public $pieceJoint01;

    public $pieceJoint02;

    public array $pieceJoint03 = [];

    public $pieceJoint04;

    public $commandeFichier;

    public $factureFournisseurFichier;

    public $titreDeTransportFichier;

    public $lesFichiers;
}
