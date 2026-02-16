<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaSoumissionFacBlDdpaDto
{
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
    public $totalMontantCommande;
    public $ratio = 0;
    public $cumul = 0;
    public $numeroDdp;
    public $dateCreation;
    public $motif;
    public $montant = 0;
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


    public function getRatio()
    {
        if ($this->totalMontantCommande == 0) {
            return 0;
        }
        return (($this->montant / $this->totalMontantCommande) * 100);
    }
}
