<?php

namespace App\Model\ddd;

use App\Model\Model;

class DemandeDiagnosticPneuModel extends Model
{
    public ?int $id = null;

    public ?string $numeroDemande = null;

    public ?int $idChantier = null;
    public ?string $codeChantier = null;
    public ?string $nomChantier = null;

    public ?int $idMateriel = null;
    public ?string $numeroParcMateriel = null;
    public ?string $marqueMateriel = null;
    public ?string $typeMateriel = null;
    public ?string $designationMateriel = null;

    public ?string $dateDepartChantier = null;

    public ?string $livraison = null;

    public ?int $nbPneuSurMachine = null;
    public ?int $nbPneuSecours = null;
    public ?int $nbPneuADiagnostiquer = null;

    public ?string $observation = null;

    /**
     * @var string[]
     */
    public array $motifs = [];

    public ?string $demandeur = null;

    public ?string $dateCreation = null;

    public ?string $statut = null;

    public ?string $numeroDit = null;

    public ?string $numeroOr = null;
}
