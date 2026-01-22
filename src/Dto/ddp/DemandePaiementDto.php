<?php

namespace App\Dto\ddp;

use App\Entity\admin\ddp\TypeDemande;

class DemandePaiementDto
{
    public string $numeroDdp;
    public $statut;
    public $adresseMailDemandeur;
    public $demandeur;
    public int $numeroVersion = 0;
    public ?TypeDemande $typeDemande = null;

    // fournisseur ======================
    public $numeroFournisseur;
    public $ribFournisseur;
    public $beneficiaire; // nom du fournisseur


    public $motif;
    public array $debiteur = [];
    public $modePaiement;

    public ?string $contact;
    public array $numeroCommande;
    public array $numeroFacture;
    public ?string $devise = null;
    public ?string $statutDossierRegul;

    public bool $estCdeClientExterneDoc = false;
    public array $nomCdeClientExterneDoc = [];
    public array $numeroDossierDouane = [];

    // piece joint ================================
    public ?string $nomAutreDoc = null;
    public bool $estAutresDoc = false;
    public $pieceJoint01;
    public $pieceJoint02;
    public ?array $pieceJoint03 = null;
    public $pieceJoint04;

    public $commandeFichier;
    public $factureFournisseurFichier;
    public $titreDeTransportFichier;
    public $lesFichiers;


    public string $montantAPayer = '0';

    // Pour le DA =====================================
    public bool $appro = false;
    public ?int $typeDa = null;
    public $montantTotalCde;
    public $montantDejaPaye;
    public $montantRestantApayer;
    public $poucentageAvance;
    public $ratioMontantpayer;

    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace(' ', '', $this->montantAPayer));
    }

    public function montantRestantApayer(): float
    {
        return $this->montantTotalCde - $this->montantDejaPaye - $this->montantAPayer();
    }

    public function pourcentageAvance(): string
    {
        return ((($this->montantDejaPaye + $this->montantAPayer()) / $this->montantTotalCde) * 100) . ' %';
    }

    public function ratioMontantpayer()
    {
        return $this->montantAPayer / $this->montantTotalCde;
    }
}
