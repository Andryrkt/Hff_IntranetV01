<?php

namespace App\Entity\planning;

class ListePlanningSearch
{
    private ?string $agence = null;
    private ?string $niveauUrgence = null;
    private ?string $interneExterne = null;
    private ?string $typeligne = null;
    private ?string $facture = null;
    private ?string $plan = null;
    private ?\DateTime $dateDebut = null;
    private ?\DateTime $dateFin = null;
    private ?string $numOr = null;
    private ?string $numSerie = null;
    private ?string $idMat = null;
    private ?string $numParc = null;
    private ?string $casier = null;
    private ?string $agenceDebite = null;
    private ?string $section = null;
    private ?bool $orBackOrder = null;
    private ?array $serviceDebite = null;
    private ?int $months = null;

    public function getAgence(): ?string
    {
        return $this->agence;
    }

    public function setAgence(?string $agence): self
    {
        $this->agence = $agence;
        return $this;
    }

    public function getNiveauUrgence(): ?string
    {
        return $this->niveauUrgence;
    }

    public function setNiveauUrgence(?string $niveauUrgence): self
    {
        $this->niveauUrgence = $niveauUrgence;
        return $this;
    }

    public function getInterneExterne(): ?string
    {
        return $this->interneExterne;
    }

    public function setInterneExterne(?string $interneExterne): self
    {
        $this->interneExterne = $interneExterne;
        return $this;
    }

    public function getTypeligne(): ?string
    {
        return $this->typeligne;
    }

    public function setTypeligne(?string $typeligne): self
    {
        $this->typeligne = $typeligne;
        return $this;
    }

    public function getFacture(): ?string
    {
        return $this->facture;
    }

    public function setFacture(?string $facture): self
    {
        $this->facture = $facture;
        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(?string $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getNumOr(): ?string
    {
        return $this->numOr;
    }

    public function setNumOr(?string $numOr): self
    {
        $this->numOr = $numOr;
        return $this;
    }

    public function getNumSerie(): ?string
    {
        return $this->numSerie;
    }

    public function setNumSerie(?string $numSerie): self
    {
        $this->numSerie = $numSerie;
        return $this;
    }

    public function getIdMat(): ?string
    {
        return $this->idMat;
    }

    public function setIdMat(?string $idMat): self
    {
        $this->idMat = $idMat;
        return $this;
    }

    public function getNumParc(): ?string
    {
        return $this->numParc;
    }

    public function setNumParc(?string $numParc): self
    {
        $this->numParc = $numParc;
        return $this;
    }

    public function getCasier(): ?string
    {
        return $this->casier;
    }

    public function setCasier(?string $casier): self
    {
        $this->casier = $casier;
        return $this;
    }

    public function getAgenceDebite(): ?string
    {
        return $this->agenceDebite;
    }

    public function setAgenceDebite(?string $agenceDebite): self
    {
        $this->agenceDebite = $agenceDebite;
        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;
        return $this;
    }

    public function getOrBackOrder(): ?bool
    {
        return $this->orBackOrder;
    }

    public function setOrBackOrder(?bool $orBackOrder): self
    {
        $this->orBackOrder = $orBackOrder;
        return $this;
    }

    public function getServiceDebite(): ?array
    {
        return $this->serviceDebite;
    }

    public function setServiceDebite(?array $serviceDebite): self
    {
        $this->serviceDebite = $serviceDebite;
        return $this;
    }

    public function getMonths(): ?int
    {
        return $this->months;
    }

    public function setMonths(?int $months): self
    {
        $this->months = $months;
        return $this;
    }
}
