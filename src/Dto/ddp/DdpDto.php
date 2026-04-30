<?php

namespace App\Dto\ddp;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DdpDto
{
    public ?string $ribFournisseur = null;
    public ?string $contact = null;
    public ?string $motif = null;
    public ?string $montantAPayer = null; // * montant qu'il faut payer au fournisseur
    public ?string $beneficiaire = null; // * nom du fournisseur 
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
}
