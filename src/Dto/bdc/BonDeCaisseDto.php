<?php

namespace App\Dto\bdc;

class BonDeCaisseDto
{
    public ?int $id = null;
    public ?string $typeDemande = null;
    public ?string $numeroDemande = null;
    public ?\DateTimeInterface $dateDemande = null;
    public ?string $caisseRetrait = null;
    public ?string $typePaiement = null;
    public ?string $agenceDebiteur = null;
    public ?string $serviceDebiteur = null;
    public ?string $debiteur = null;
    public ?string $emetteur = null;
    public ?string $retraitLie = null;
    public ?string $matricule = null;
    public ?string $adresseMailDemandeur = null;
    public ?string $motifDemande = null;
    public ?string $montantPayer = null;
    public ?string $devise = null;
    public ?string $statutDemande = null;
    public ?\DateTimeInterface $dateStatut = null;
    public ?string $pdfDemande = null;
    public ?string $nomValidateurFinal = null;

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
