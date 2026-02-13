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
    public ?int $cumul = 0;
}
