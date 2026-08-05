<?php

namespace App\Dto\ddd;

use App\Entity\ddd\Chantier;
use DateTime;

class DemandeDiagnosticPneuDto
{
    public ?int $id = null;

    public ?string $numeroDemande = null;

    public ?Chantier $chantier = null;
    public ?int $idChantier = null;
    public ?string $codeChantier = null;
    public ?string $nomChantier = null;

    public ?int $idMateriel = null;
    public ?string $numeroParcMateriel = null;
    public ?string $marqueMateriel = null;
    public ?string $typeMateriel = null;
    public ?string $designationMateriel = null;

    public ?DateTime $dateDepartChantier = null;

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

    public ?DateTime $dateCreation = null;

    public ?string $statut = null;

    public ?string $numeroDit = null;

    public ?string $numeroOr = null;
}
