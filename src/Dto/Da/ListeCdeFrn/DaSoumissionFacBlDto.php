<?php


namespace App\Dto\Da\ListeCdeFrn;


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
    public $numeroDdp;
    public $typeDemande;
    public $ribFournisseur;
    public $beneficiaire;
    public $motif = null;
    public $debiteur = [];
    public $statut;
    public $adresseMailDemandeur;
    public $demandeur;
    public $modePaiement;
    public $montantAPayer;
    public $contact = null;
    public $numeroCommande = [];
    public $numeroFacture = [];
    public $devise;
    public $statutDossierRegul = null;
    public $numeroVersion = 1;
    public $estAutresDoc = false;
    public $nomAutreDoc = null;
    public $estCdeClientExterneDoc = false;
    public $nomCdeClientExterneDoc = null;
    public $numeroDossierDouane = [];
    public $appro = false;
    public $typeDa;
    public $numeroVersionBc;
    public $nomAvecCheminFichierDistant;
    public $dateCreation;
    public $lesFichiers = [];
    public $typeDdp;

    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace('.', '', $this->montantAPayer));
    }
}
