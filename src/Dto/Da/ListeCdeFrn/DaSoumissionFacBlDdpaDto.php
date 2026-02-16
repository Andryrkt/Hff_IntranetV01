<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaSoumissionFacBlDdpaDto
{
    // demande appro ====
    public $numeroDemandeAppro;

    // OR =====
    public $numeroOR;

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
    public $totalMontantCommande;
    public $totalPayer = 0;
    public $ratioTotalPayer = 0;
    public $montantAregulariser;
    public $ratioMontantARegul;

    public $const;
    public $ref;
    public $designation;
    public $qteCde;
    public $qteReceptionnee;
    public $qteReliquat;
    public $statutRecep;
    public $receptions = [];

}
