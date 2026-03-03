<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaSoumissionFacBlDdpaDto
{
    // demande appro ====
    public $numeroDemandeAppro;

    // OR  et DIT=====
    public $numeroOR;
    public $numeroDemandeDit;

    // commande ========
    public $numeroCde;
    public $montantTotalCde;

    // concerne la soumision =====
    public $pieceJoint1;
    public $pieceJoint2;
    public $utilisateur;
    public $numeroVersionFacBl;
    public $statutFacBl;

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
    public $numeroFournisseur;
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


    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace('.', '', $this->montantAPayer));
    }
}
