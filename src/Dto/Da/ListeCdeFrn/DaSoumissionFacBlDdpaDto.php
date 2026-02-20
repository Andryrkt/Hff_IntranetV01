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
    public $numeroVersion;
    public $statut;

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
}
