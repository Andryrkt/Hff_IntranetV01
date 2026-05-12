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
    public ?DateTime $dateClotLiv = null;
    public  $pieceJoint1 = null; // type: fichier et string
    public  $pieceJoint2 = null; // type: fichier et string
    public ?string $utilisateur = null;
    public float $totalMontantPayer = 0.0;
    public DateTime $dateDemande;
    public ?string $codeSociete = null;
    public bool $estRegule = false;
    public ?string $dernierStatutDdp = null;

    // livraison ========================
    public $numLiv; // type : tableau et string
    public array $infoLiv = [];

    // Bon à payer (BAP) ====================
    public ?string $numeroBap = null;
    public ?string $statutBap = null;
    public ?DateTime $dateSoumissionCompta = null;
    public float $montantBlFacture = 0.0;
    public float $montantReceptionIps = 0.0;
    public ?string $numeroDemandePaiement = null;
    public ?DateTime $dateStatutBap = null;

    // info commande (BC) =================
    public ?string $numeroFournisseur = null;
    public ?string $nomFournisseur = null;
    public ?string $numeroFactureFournisseur = null;
    public array $infoBc = [];

    // Reappro =====================
    public bool $estfactureReappro = false;
    public ?string $numerofactureReappro = null;

    // CLA =========================
    public ?string $numeroCla = null;

    // DDPL =========================
    public ?string $statutFacBl = null;
    public ?string $numeroVersionFacBl = null;

    // si DA qui a une Demande de paiement à l'avance
    public array $daDdpa = [];
    public string $titreDaDdpa = "Liste des demandes de paiement à l'avance";
    public float $totalMontantCommande = 0.0;
    public float $montantDejaPaye = 0.0;
    public float $ratioMontantDejaPaye = 0.0;
    public float $montantAregulariser = 0.0;
    public float $ratioMontantARegul = 0.0;

    // situation reception 
    public array $receptions = [];

    // demande de paiement ===========================
    public ?DemandePaiementDto $demandePaiementDto = null;
    public float $montantAPayer = 0.0;
    public ?string $typeDdp = null; // valeur possible 'bap', 'ddpl', 'regule', 'aucune'



    public function montantAPayer(): float
    {
        return (float)str_replace(',', '.', str_replace('.', '', $this->montantAPayer));
    }
}
