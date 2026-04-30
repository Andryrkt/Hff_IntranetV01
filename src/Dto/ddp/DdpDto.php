<?php

namespace App\Dto\ddp;

use App\Entity\admin\ddp\TypeDemande;
use App\Service\TableauEnStringService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DdpDto
{
    public ?TypeDemande $typeDdp = null;
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


    public function getNumeroCommandeString(): string
    {
        return TableauEnStringService::TableauEnString(',', $this->numeroCommande);
    }

    public function getNumeroFactureString(): string
    {
        return TableauEnStringService::TableauEnString(',', $this->numeroFacture);
    }
}
