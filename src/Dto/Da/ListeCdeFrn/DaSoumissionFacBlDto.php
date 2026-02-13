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
    public $statut;
    public $pieceJoint1;
    public $pieceJoint2;
    public $utilisateur;
    public $numeroVersion;

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
}
