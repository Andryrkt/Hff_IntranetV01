<?php

namespace App\Dto\ddp;

use App\Entity\admin\ddp\TypeDemande;
use App\Service\TableauEnStringService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DdpDto
{

    // pour le forulaire =====================
    public ?string $contact = null;
    public ?string $motif = null;
    public ?string $montantAPayer = null; // * montant qu'il faut payer au fournisseur (à l'avance : montant facture proforma)
    public ?string $numeroFactureProforma = null; // * n° facture proforma (à l'avance), enregistré comme numeroFacture
    public ?string $montantTotalCommande = null; // * montant total du commande (à l'avance)
    // numéro de la commande
    public array $numeroCommande = [];
    public array $montantsCommande = []; // [numeroCommande => montant du commande réceptionné] lu dans IPS à la soumission (après arrivage)
    public array $montantsTotalCommande = []; // [numeroCommande => montant total de la commande] lu dans IPS à la soumission (après arrivage)
    public array $soldesAvance = []; // [numeroCommande => solde avance] calculé à la soumission (après arrivage)
    public array $commandesRegule = []; // commandes déjà payées à 100 % par les DDPA : rien de plus à payer (après arrivage)
    public array $donneesPageDeGarde = []; // tableaux de la page de garde après arrivage (voir DdpController::donneesPageDeGarde)
    // numéro de la facture
    public array $numeroFacture = [];
    public array $livraisonsParCommande = []; // [numeroCommande => numéros de livraison] des factures sélectionnées (après arrivage)
    public array $lignesFactures = []; // couples facture/commande (DemandePaiementModel::getCommandesParFacture, après arrivage)
    // fournisseur
    public ?string $numeroFournisseur = null;
    public ?string $beneficiaire = null; // * nom du fournisseur 
    public ?string $ribFournisseur = null;
    // RIB connus du fournisseur dans IPS (pour détecter un RIB mis à jour)
    public array $ribsFournisseurAncien = [];
    // Mode paiement
    public ?string $modePaiement = null;
    public array $choiceModePaiement = [];
    // devise
    public ?string $devise = null;
    public array $choiceDevise = [];

    // agence et service débiteur
    public array $debiteur = [];

    // Fichiers
    public ?UploadedFile $pieceJoint01 = null;
    public ?UploadedFile $pieceJoint02 = null;
    public ?UploadedFile $pieceJoint03 = null;
    public ?UploadedFile $pieceJoint04 = null;

    // proprieter à assigner apres soumission =========================
    public ?string $numeroDdp = null;

    // autre doc--------
    // pour pieceJoint04
    public bool $estAutreDoc = false;
    public ?string $nomAutreDoc = null;
    // pour pieceJoint03
    public bool $estCdeClientExterneDoc = false;
    public array $nomCdeClientExterneDoc = [];
    // pour le chemin, nom des fichier uploder
    public array $nomEtCheminFichiersEnregistrer = [];
    public array $nomFichierTelecharger = [];
    public ?string $nomAvecCheminFichier = null;
    public ?string $nomFichier = null;
    // pour les nom de fichiers dans DW
    public array $nomDesFichiersDwCommande = [];
    // pour les nom des fichiers distant dans 192.168.0.15
    public array $nomDesFichiersDistant = [];
    public array $lesFichiers = [];


    // info sur l'utilisateur --------------------
    public ?string $adresseMailDemandeur = null;
    public ?string $demandeur = null;

    // info utile -------------
    public ?string $statut = null;
    public int $numeroVersion = 0;
    public array $numeroDossierDouane = [];
    public array $lesFichiersFusionner = [];
    public ?TypeDemande $typeDdp = null;
    public ?string $codeSociete = 'HF';


    public ?\DateTime $dateDemande = null;


    public bool $isFrnNonImmatricule = false;
    public bool $appro = false;

    // pourcentage du montant à payer par rapport au montant HT total des commandes
    // (colonne ratio_montant_payer de demande_paiement_ligne)
    public float $ratioMontantPayer = 0.0;

    /**
     * Transformation du montant à payer en float
     */
    public function montantAPayer(): float
    {
        return self::montantEnFloat($this->montantAPayer);
    }

    /**
     * Transformation du montant total du commande en float (null si non renseigné)
     */
    public function montantTotalCommande(): ?float
    {
        if ($this->montantTotalCommande === null || trim($this->montantTotalCommande) === '') {
            return null;
        }
        return self::montantEnFloat($this->montantTotalCommande);
    }

    /**
     * Montant du commande réceptionné, en float (null si non renseigné)
     */
    public function montantCommande(?string $numeroCommande): ?float
    {
        $montant = $this->montantsCommande[$numeroCommande] ?? null;
        if ($montant === null || trim($montant) === '') {
            return null;
        }
        return self::montantEnFloat($montant);
    }

    /**
     * Montant du commande à payer (après arrivage) : 0 pour une commande déjà payée à 100 % par les DDPA ou si le solde avance couvre le montant
     * réceptionné, sinon la part du réceptionné au-delà du solde (null si le réceptionné n'est pas renseigné).
     * Le front en affiche un aperçu : DemandePaiementManager.js::afficherMontantCommandeAPayer
     */
    public function montantCommandeAPayer(?string $numeroCommande): ?float
    {
        $receptionne = $this->montantCommande($numeroCommande);
        if ($receptionne === null) {
            return null;
        }

        if (in_array((string) $numeroCommande, $this->commandesRegule, true)) {
            return 0.0;
        }

        $solde = (float) ($this->soldesAvance[$numeroCommande] ?? 0);

        return $solde > 0 && $receptionne <= $solde ? 0.0 : $receptionne - $solde;
    }

    /**
     * Solde avance restant (après arrivage) : solde avance − réceptionné si le solde couvre le montant réceptionné, sinon 0
     * (null si le réceptionné n'est pas renseigné).
     * Le front en affiche un aperçu : DemandePaiementManager.js::afficherMontantCommandeAPayer
     */
    public function soldeAvanceRestant(?string $numeroCommande): ?float
    {
        $receptionne = $this->montantCommande($numeroCommande);
        if ($receptionne === null) {
            return null;
        }

        $solde = (float) ($this->soldesAvance[$numeroCommande] ?? 0);

        return $solde > 0 && $receptionne <= $solde ? $solde - $receptionne : 0.0;
    }

    /**
     * Conversion d'un montant saisi (ex: "1 234,56") en float
     */
    private static function montantEnFloat($montant): float
    {
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

    /**
     * Le RIB saisi diffère-t-il de ceux connus pour le fournisseur ?
     * (comparaison sans les espaces)
     */
    public function ribFournisseurChanger(): bool
    {
        $nouveau = str_replace(' ', '', (string) $this->ribFournisseur);
        if ($nouveau === '') {
            return false;
        }

        $anciens = array_map(fn($rib) => str_replace(' ', '', (string) $rib), $this->ribsFournisseurAncien);

        return !in_array($nouveau, $anciens, true);
    }

    /**
     * Récupération de tous les noms des fichiers
     * dans une seul tableau
     *
     * @return array
     */
    public function getToutesLesNomFichiers(): array
    {
        return array_merge($this->nomDesFichiersDistant, $this->nomFichierTelecharger, $this->nomDesFichiersDwCommande);
    }


    /**
     * Transformation du numero commande en chaine de caractère quotée, séparée par une virgule
     * (pour une clause SQL IN (...))
     */
    public function getNumeroCommandeString(): string
    {
        return is_array($this->numeroCommande) ? TableauEnStringService::TableauEnString(',', $this->numeroCommande) : $this->numeroCommande;
    }

    /**
     * Transformation du numero facture en chaine de caractère quotée, séparée par une virgule
     * (pour une clause SQL IN (...))
     */
    public function getNumeroFactureString(): string
    {
        return is_array($this->numeroFacture) ? TableauEnStringService::TableauEnString(',', $this->numeroFacture) : $this->numeroFacture;
    }

    /**
     * Transformation du numero commande en chaine de caractère séparée par un point virgule,
     * sans guillemets (pour stockage en base, contrairement à getNumeroCommandeString() qui est destiné aux clauses SQL)
     */
    public function numeroCommandeSansGuillemet(): string
    {
        return is_array($this->numeroCommande) ? implode(';', $this->numeroCommande) : $this->numeroCommande;
    }

    /**
     * Transformation du numero facture en chaine de caractère séparée par un point virgule,
     * sans guillemets (pour stockage en base, contrairement à getNumeroFactureString() qui est destiné aux clauses SQL)
     */
    public function numeroFactureSansGuillemet(): string
    {
        return is_array($this->numeroFacture) ? implode(';', $this->numeroFacture) : $this->numeroFacture;
    }

    /**
     * Formatage de date de demande de paiement
     *
     * @return void
     */
    public function dateDemandeFormater()
    {
        return $this->dateDemande->format('d/m/Y');
    }
}
