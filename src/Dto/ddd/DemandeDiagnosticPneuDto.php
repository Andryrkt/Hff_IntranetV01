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
    public ?array $piecesJointes = [];


    // AJOUT : Champs de DemandeIntervention
    public ?int $demandeInterventionId = null;  // ID de l'intervention
    public ?string $numeroOrIntervention = null;  // Numéro OR depuis l'intervention
    public ?string $statutIntervention = null;  // Statut de l'intervention
    public ?DateTime $dateCreationIntervention = null;  // Date de création de l'intervention
    public ?string $typeIntervention = null;  // Type d'intervention si besoin

    public function __construct()
    {
        // Constructeur vide pour permettre la création via setters
    }
    // Méthode de fabrique pour créer le DTO à partir des entités
    public static function fromEntities($demande, $intervention = null): self
    {
        $dto = new self();
        // Remplir les données de DemandeDiagnosticPneu
        $dto->id = $demande->getId();
        $dto->numeroDemande = $demande->getNumeroDemande();

        // Chantier
        $chantier = $demande->getChantier();
        if ($chantier) {
            $dto->chantier = $chantier;
            $dto->idChantier = $chantier->getId();
            // Adaptez les noms de méthodes selon votre entité Chantier
            $dto->codeChantier = $chantier->getCodeChantier() ?? $chantier->getCode();
            $dto->nomChantier = $chantier->getNomChantier() ?? $chantier->getNom();
        }

        // Matériel (les champs sont directement dans l'entité)
        $dto->idMateriel = $demande->getIdMateriel();
        $dto->numeroParcMateriel = $demande->getNumeroParcMateriel();
        $dto->marqueMateriel = $demande->getMarqueMateriel();
        $dto->typeMateriel = $demande->getTypeMateriel();
        $dto->designationMateriel = $demande->getDesignationMateriel();

        $dto->dateDepartChantier = $demande->getDateDepartChantier();
        $dto->livraison = $demande->getLivraison();
        $dto->nbPneuSurMachine = $demande->getNbPneuSurMachine();
        $dto->nbPneuSecours = $demande->getNbPneuSecours();
        $dto->nbPneuADiagnostiquer = $demande->getNbPneuADiagnostiquer();
        $dto->observation = $demande->getObservation();
        $dto->motifs = $demande->getMotifs() ?? [];
        $dto->demandeur = $demande->getDemandeur();
        $dto->dateCreation = $demande->getDateCreation();
        $dto->statut = $demande->getStatut();

        $dto->piecesJointes = $demande->getPiecesJointes() ?? [];

        // Remplir les données de DemandeIntervention si disponible
        if ($intervention) {
            $dto->numeroDit = $demande->getNumeroDit();
            $dto->demandeInterventionId = $intervention->getId();
            $dto->numeroOr = $demande->getNumeroOr();
        }

        return $dto;
    }

    // Getters et Setters si nécessaire
    public function getDemandeInterventionId(): ?int
    {
        return $this->demandeInterventionId;
    }

    public function setDemandeInterventionId(?int $demandeInterventionId): self
    {
        $this->demandeInterventionId = $demandeInterventionId;
        return $this;
    }

    public function getNumeroDit(): ?string
    {
        return $this->numeroDit;
    }

    public function setNumeroDit(?string $numeroDit): self
    {
        $this->numeroDit = $numeroDit;
        return $this;
    }

    public function getNumeroOrIntervention(): ?string
    {
        return $this->numeroOrIntervention;
    }

    public function setNumeroOrIntervention(?string $numeroOrIntervention): self
    {
        $this->numeroOrIntervention = $numeroOrIntervention;
        return $this;
    }

    public function getStatutIntervention(): ?string
    {
        return $this->statutIntervention;
    }

    public function setStatutIntervention(?string $statutIntervention): self
    {
        $this->statutIntervention = $statutIntervention;
        return $this;
    }

    public function getDateCreationIntervention(): ?DateTime
    {
        return $this->dateCreationIntervention;
    }

    public function setDateCreationIntervention(?DateTime $dateCreationIntervention): self
    {
        $this->dateCreationIntervention = $dateCreationIntervention;
        return $this;
    }

    public function getTypeIntervention(): ?string
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(?string $typeIntervention): self
    {
        $this->typeIntervention = $typeIntervention;
        return $this;
    }
}
