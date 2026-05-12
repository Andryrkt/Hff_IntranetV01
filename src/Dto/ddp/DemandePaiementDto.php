<?php

namespace App\Dto\ddp;

use App\Constants\ddp\StatutConstants;
use App\Entity\admin\ddp\TypeDemande;
use App\Traits\ChaineCaractereTrait;

class DemandePaiementDto
{
    use ChaineCaractereTrait;

    // info generale =====================
    public string $numeroDdp;
    public string $statut;
    public string $adresseMailDemandeur;
    public string $demandeur;
    public int $numeroVersion = 0;
    public ?TypeDemande $typeDemande = null;
    public ?\DateTime $dateDemande = null;
    public bool $estChangementDeRib = false;
    public ?string $numeroCla = null;
    public ?\DateTime $dateSoumissionCompta = null;
    public ?string $codeAgence = null;
    public ?string $codeService = null;
    public bool $ddpSoumissioncde = false;
    public ?string $codeSociete = null;

    public ?string $numeroFactureIps = null;
    public bool $estRegule = false;
    public ?string $dernierStatutDdp = null;

    // fournisseur ======================
    public ?string $numeroFournisseur = null;
    public ?string $ribFournisseur = null;
    public ?string $ribFournisseurAncien = null;
    public ?string $cif = null;
    public ?string $beneficiaire = null; // nom du fournisseur
    public ?string $modePaiement = null;
    public ?string $devise = null;
    public ?string $contact = null;

    // info sur Ddp =========================
    public ?string $motif = null;
    public array $debiteur = [];
    public ?string  $numeroCommande = null;
    public ?string $numeroFacture = null;
    public ?string $statutDossierRegul = null;

    public bool $estCdeClientExterneDoc = false;
    public array $nomCdeClientExterneDoc = [];

    public array $ddpRecap = [];

    // piece joint ================================
    public array $numeroDossierDouane = [];
    public ?string $nomAutreDoc = null;
    public bool $estAutresDoc = false;
    public $pieceJoint01;
    public $pieceJoint02;
    public $pieceJoint03;
    public $pieceJoint04;
    public array $fichiersChoisis = [];

    public $commandeFichier;
    public $factureFournisseurFichier;
    public $titreDeTransportFichier;
    public array $lesFichiers = [];



    // Pour le DA et les montants =====================================
    public bool $appro = false;
    public ?int $typeDa = null;
    public int $numeroSoumissionDdpDa;
    public string $numeroDemandeAppro;
    public ?string $numeroLivraison = null;
    public string $numeroDa;
    public bool $ddpaDa = false;
    public int $numeroVersionBc = 0;
    public string $nomPdfFusionnerBc = '';
    public array $daDdpa = [];
    public string $titreDaDdpa = "historique des demandes de paiement à l'avance déjà effectuées dans le formulaire.";


    public float $totalMontantCommande = 0;
    public float $montantDejaPaye = 0; // montant total des payement déjà effectuer
    public float $ratioMontantDejaPaye = 0.0; // pourcentage total des payements déjà effectuer
    public float $montantRestantApayer = 0; // montant total de la commande - montant déjà payé
    public string $pourcentageAvance; // pourcentage de montant déjà payer + pourcentage de montant à regulariser (ou à payer)

    // ** les variables suivants sont les mêmes seule le  nom qui le différencie
    public string $montantAPayer = '0'; // c'est le montant qu'on va payer ou à regulariser
    public float $montantAregulariser;
    public float $ratioMontantARegul;
    public float $pourcentageAPayer = 0.0;

    public function montantAPayer(): float
    {
        $montant = $this->montantAPayer;
        if (is_string($montant)) {
            if (strpos($montant, ',') !== false) {
                $montant = str_replace([' ', '.'], '', $montant);
                $montant = str_replace(',', '.', $montant);
            } else {
                $montant = str_replace(' ', '', $montant);
            }
        }
        return (float) $montant;
    }


    public function numCdeString()
    {
        return is_array($this->numeroCommande) ? implode(';', $this->numeroCommande) : $this->numeroCommande;
    }

    public function numFacString()
    {
        return is_array($this->numeroFacture) ? implode(';', $this->numeroFacture) : $this->numeroFacture;
    }

    public function numeroDossierDouaneString()
    {
        implode(";", $this->numeroDossierDouane);
    }

    public function dateDemandeFormater()
    {
        return $this->dateDemande->format('d/m/Y');
    }

    public function lesFichiersStringSansExtension()
    {
        return implode(";", $this->removePdfExtension($this->lesFichiers));
    }

    public function lesFichiersStringAvecExtension()
    {
        return implode(";", $this->lesFichiers);
    }

    public function ribFournisseurChanger(): bool
    {
        $ancien = str_replace(' ', '', (string)$this->ribFournisseurAncien);
        $nouveau = str_replace(' ', '', (string)$this->ribFournisseur);

        return $ancien !== $nouveau && !empty($nouveau);
    }

    public function getStyleStatut(): string
    {
        return StatutConstants::getCssClass($this->statut);
    }

    public function estStatutATransmettre(): bool
    {
        return in_array($this->statut, StatutConstants::STATUT_A_TRANSMETTRE);
    }
}
