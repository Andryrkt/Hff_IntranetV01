<?php

namespace App\Entity\ddd;

class DemandeDiagnosticPneuSearch
{
    private ?string $numeroDemande = null;
    private ?string $demandeur = null;
    private ?int $idChantier = null;
    private ?string $statut = null;
    private ?\DateTime $dateCreationDebut = null;
    private ?\DateTime $dateCreationFin = null;
    private ?string $numeroParcMateriel = null;
    private ?string $numeroDit = null;
    private ?string $numeroOr = null;
    private ?string $livraison = null;

    // ----- Getters et Setters -----

    public function getNumeroDemande(): ?string
    {
        return $this->numeroDemande;
    }

    public function setNumeroDemande(?string $numeroDemande): self
    {
        $this->numeroDemande = $numeroDemande;
        return $this;
    }

    public function getDemandeur(): ?string
    {
        return $this->demandeur;
    }

    public function setDemandeur(?string $demandeur): self
    {
        $this->demandeur = $demandeur;
        return $this;
    }

    public function getIdChantier(): ?int
    {
        return $this->idChantier;
    }

    public function setIdChantier(?int $idChantier): self
    {
        $this->idChantier = $idChantier;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateCreationDebut(): ?\DateTime
    {
        return $this->dateCreationDebut;
    }

    public function setDateCreationDebut(?\DateTime $dateCreationDebut): self
    {
        $this->dateCreationDebut = $dateCreationDebut;
        return $this;
    }

    public function getDateCreationFin(): ?\DateTime
    {
        return $this->dateCreationFin;
    }

    public function setDateCreationFin(?\DateTime $dateCreationFin): self
    {
        $this->dateCreationFin = $dateCreationFin;
        return $this;
    }

    public function getNumeroParcMateriel(): ?string
    {
        return $this->numeroParcMateriel;
    }

    public function setNumeroParcMateriel(?string $numeroParcMateriel): self
    {
        $this->numeroParcMateriel = $numeroParcMateriel;
        return $this;
    }

    // ----- Nouveaux getters/setters -----

    public function getNumeroDit(): ?string
    {
        return $this->numeroDit;
    }

    public function setNumeroDit(?string $numeroDit): self
    {
        $this->numeroDit = $numeroDit;
        return $this;
    }

    public function getNumeroOr(): ?string
    {
        return $this->numeroOr;
    }

    public function setNumeroOr(?string $numeroOr): self
    {
        $this->numeroOr = $numeroOr;
        return $this;
    }

    public function getLivraison(): ?string
    {
        return $this->livraison;
    }

    public function setLivraison(?string $livraison): self
    {
        $this->livraison = $livraison;
        return $this;
    }

    // ----- Conversion méthodes -----

    /**
     * Convertit l'objet en tableau associatif
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'numeroDemande'        => $this->numeroDemande,
            'demandeur'            => $this->demandeur,
            'idChantier'           => $this->idChantier,
            'statut'               => $this->statut,
            'dateCreationDebut'    => $this->dateCreationDebut,
            'dateCreationFin'      => $this->dateCreationFin,
            'numeroParcMateriel'   => $this->numeroParcMateriel,
            'numeroDit'            => $this->numeroDit,
            'numeroOr'             => $this->numeroOr,
            'livraison'            => $this->livraison,
        ];
    }

    /**
     * Hydrate l'objet à partir d'un tableau associatif
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): void
    {
        $this->numeroDemande       = $data['numeroDemande'] ?? null;
        $this->demandeur           = $data['demandeur'] ?? null;
        $this->idChantier          = $data['idChantier'] ?? null;
        $this->statut              = $data['statut'] ?? null;
        $this->dateCreationDebut   = $data['dateCreationDebut'] ?? null;
        $this->dateCreationFin     = $data['dateCreationFin'] ?? null;
        $this->numeroParcMateriel  = $data['numeroParcMateriel'] ?? null;
        $this->numeroDit           = $data['numeroDit'] ?? null;
        $this->numeroOr            = $data['numeroOr'] ?? null;
        $this->livraison           = $data['livraison'] ?? null;
    }
}
