<?php

namespace App\Dto\Da\ListeCdeFrn;



class DaSoumissionBcDto
{
    public ?string $numeroCde = null;
    public ?string $numeroDemandeAppro = null;
    public ?string $numeroDemandeDit = null;
    public ?string $numeroOR = null;
    public ?string $statut = null;
    public ?string $utilisateur = null;
    public ?int $numeroVersion = null;
    public $pieceJoint1;
    public ?array $pieceJoint2 = [];
    public ?float $montantBc = null;
    public ?bool $demandePaiementAvance = null;
}
