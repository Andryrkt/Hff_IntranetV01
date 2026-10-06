<?php

namespace App\Dto\ddp;

use App\Entity\admin\ddp\TypeDemandePaiement;

class DdpSearchDto
{
    public ?array $debiteur = [];
    public ?TypeDemandePaiement $typeDemande = null;
    public ?string $numDdp = null;
    public ?string $numCommande = null;
    public ?string $numFacture = null;
    public ?string $utilisateur = null;
    public ?array $dateCreation = [];
    public ?string $statut = null;
    public ?string $fournisseur = null;
}
