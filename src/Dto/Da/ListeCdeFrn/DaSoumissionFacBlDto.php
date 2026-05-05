<?php


namespace App\Dto\Da\ListeCdeFrn;

use App\Dto\ddp\DemandePaiementDto;
use DateTime;

class DaSoumissionFacBlDto
{
    public ?string $numeroDemandeAppro = null;
    public ?string $numeroDemandeDit = null;
    public ?string $numeroOR = null;
    public ?string $numeroCde = null;
    public ?string $refBlFac = null;
    public ?DateTime $dateBlFac = null;
    public $dateClotLiv;
    public $pieceJoint1;
    public $pieceJoint2;
    public ?string $utilisateur = null;
    public $totalMontantPayer;
    public DateTime $dateDemande;
    public ?string $codeSociete = null;

    // livraison ========================
    public $numLiv;
    public $infoLiv;

    // Bon à payer (BAP) ====================
    public ?string $numeroBap = null;
    public ?string $statutBap = null;
    public $dateSoumissionCompta;
    public $montantBlFacture;
    public $montantReceptionIps;
    public ?string $numeroDemandePaiement;
    public $dateStatutBap = null;

    // info commande (BC) =================
    public $numeroFournisseur;
    public $nomFournisseur;
    public $numeroFactureFournisseur;
    public $infoBc;

    // Reappro =====================
    public $estfactureReappro = false;
    public $numerofactureReappro = null;

    // CLA =========================
    public ?string $numeroCla = null;

    // DDPL =========================
    public ?string $statutFacBl = null;
    public ?string $numeroVersionFacBl = null;

    // si DA qui a une Demande de paiement à l'avance
    public array $daDdpa = [];
    public string $titreDaDdpa = "Liste des demandes de paiement à l'avance";
    public $totalMontantCommande;
    public $totalPayer = 0;
    public $ratioTotalPayer = 0;
    public $montantAregulariser;
    public $ratioMontantARegul;

    // situation reception 
    public array $receptions = [];

    // demande de paiement ===========================
    public ?DemandePaiementDto $demandePaiementDto = null;
    public $montantAPayer;
    public $typeDdp;


    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace('.', '', $this->montantAPayer));
    }
}
