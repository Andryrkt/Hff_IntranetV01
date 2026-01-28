<?php

namespace App\Dto\ddp;

use App\Traits\ChaineCaractereTrait;
use App\Entity\admin\ddp\TypeDemande;

class DemandePaiementDto
{
    use ChaineCaractereTrait;

    // info generale =====================
    public string $numeroDdp;
    public $statut;
    public $adresseMailDemandeur;
    public $demandeur;
    public int $numeroVersion = 0;
    public ?TypeDemande $typeDemande = null;
    public ?\DateTime $dateDemande = null;
    public bool $estChangementDeRib = false;

    // fournisseur ======================
    public $numeroFournisseur;
    public $ribFournisseur;
    public $beneficiaire; // nom du fournisseur
    public $modePaiement;
    public ?string $devise = null;
    public ?string $contact = null;

    // info sur Ddp =========================
    public $motif;
    public array $debiteur = [];
    public array $numeroCommande;
    public array $numeroFacture;
    public ?string $statutDossierRegul = null;

    public bool $estCdeClientExterneDoc = false;
    public array $nomCdeClientExterneDoc = [];

    // piece joint ================================
    public array $numeroDossierDouane = [];
    public ?string $nomAutreDoc = null;
    public bool $estAutresDoc = false;
    public $pieceJoint01;
    public $pieceJoint02;
    public $pieceJoint03;
    public $pieceJoint04;

    public $commandeFichier;
    public $factureFournisseurFichier;
    public $titreDeTransportFichier;
    public $lesFichiers;



    // Pour le DA et les montants =====================================
    public bool $appro = false;
    public ?int $typeDa = null;
    public string $montantAPayer = '0';
    public int $pourcentageAPayer;
    public $montantTotalCde;
    public $montantDejaPaye;
    public $montantRestantApayer;
    public $pourcentageAvance;
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

    public function numCdeString()
    {
        return implode(';', $this->numeroCommande);
    }

    public function numFacString()
    {
        return implode(';', $this->numeroFacture);
    }

    public function numeroDossierDouaneString()
    {
        implode(";", $this->numeroDossierDouane);
    }

    public function dateDemandeFormater()
    {
        return $this->dateDemande->format('d/m/Y');
    }

    public function lesFichiersStringSansExtension()
    {
        return implode(";", $this->removePdfExtension($this->lesFichiers));
    }

    public function lesFichiersStringAvecExtension()
    {
        return implode(";", $this->lesFichiers);
    }
}
