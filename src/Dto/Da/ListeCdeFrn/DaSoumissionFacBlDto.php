<?php


namespace App\Dto\Da\ListeCdeFrn;

use App\Dto\ddp\DemandePaiementDto;

class DaSoumissionFacBlDto
{
    public $numeroDemandeAppro;
    public $numeroDemandeDit;
    public $numeroOR;
    public $numeroCde;
    public $refBlFac;
    public $dateBlFac;
    public $dateClotLiv;
    public $pieceJoint1;
    public $pieceJoint2;
    public $utilisateur;
    public $totalMontantPayer;

    // livraison ========================
    public $numLiv;
    public $infoLiv;

    // Bon à payer (BAP) ====================
    public $numeroBap = null;
    public $statutBap = null;
    public $dateSoumissionCompta;
    public $montantBlFacture;
    public $montantReceptionIps;
    public $numeroDemandePaiement;
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
    public $numeroCla = null;

    // DDPL =========================
    public $statutFacBl;
    public $numeroVersionFacBl;

    // si DA qui a une Demande de paiement à l'avance
    public $daDdpa = [];
    public $titreDaDdpa = "Liste des demandes de paiement à l'avance";
    public $totalMontantCommande;
    public $totalPayer = 0;
    public $ratioTotalPayer = 0;
    public $montantAregulariser;
    public $ratioMontantARegul;

    // situation reception 
    public $receptions = [];

    // demande de paiement ===========================
    public ?DemandePaiementDto $demandePaiementDto = null;
    public $montantAPayer;
    public $typeDdp;


    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace('.', '', $this->montantAPayer));
    }
}
