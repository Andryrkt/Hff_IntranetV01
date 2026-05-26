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
    public ?string $montantAPayer = null; // * montant qu'il faut payer au fournisseur
    // numéro de la commande
    public array $numeroCommande = [];
    // numéro de la facture
    public array $numeroFacture = [];
    // fournisseur
    public ?string $numeroFournisseur = null;
    public ?string $beneficiaire = null; // * nom du fournisseur 
    public ?string $ribFournisseur = null;
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


    // Demande Appro -------------------------------
    public ?string $numeroSoumissionDdpDa = null;
    public ?string $numeroDemandeAppro = null;
    public ?string $numeroVersionBc = null;
    public bool $estAppro = false;
    public ?string $typeDa = null;
    // utile seulement pour le demande de paiement à l'avance d'une DA avec soumission BC
    public bool $ddpSoumissioncde = false;



    /**
     * Transformation du montant à payer en float
     */
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
     * Transformation du numero commande en chaine de caractère séparer par une point virgule
     */
    public function getNumeroCommandeString(): string
    {
        return is_array($this->numeroCommande) ? TableauEnStringService::TableauEnString(';', $this->numeroCommande) : $this->numeroCommande;
    }

    /**
     * Transformation du numero facture en chaine de caractère séparer par une point virgule
     */
    public function getNumeroFactureString(): string
    {
        return is_array($this->numeroFacture) ? TableauEnStringService::TableauEnString(';', $this->numeroFacture) : $this->numeroFacture;
    }
}
