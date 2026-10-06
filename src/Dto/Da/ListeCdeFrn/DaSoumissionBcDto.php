<?php

namespace App\Dto\Da\ListeCdeFrn;



class DaSoumissionBcDto
{
    public ?string $numeroDemandeAppro = null;
    public ?string $numeroDemandeDit = null;
    public ?string $numeroOr = null;
    public ?string $numeroCde = null;
    public ?string $statut = null;
    public $pieceJoint1;
    public ?string $utilisateur = null;
    public ?int $numeroVersion = null;
    public ?float $montantBc = null;
    public ?bool $demandePaiementAvance = null;
    public ?string $numeroDemandePaiement = null;
    public ?string $codeSociete = null;

    public ?array $pieceJoint2 = [];
    public ?int $typeDa = null;
    public ?float $montantBcIps = null;
}
