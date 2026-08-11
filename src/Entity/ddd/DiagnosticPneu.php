<?php

namespace App\Entity\ddd;

use App\Repository\ddd\DiagnosticPneuRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DiagnosticPneuRepository::class)
 * @ORM\Table(name="diagnostic_pneu")
 */
class DiagnosticPneu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=DemandeDiagnosticPneu::class, inversedBy="diagnosticPneus")
     * @ORM\JoinColumn(name="id_demande", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?DemandeDiagnosticPneu $demande = null;

    /**
     * @ORM\Column(type="smallint", name="numero_ligne")
     * @Assert\Range(min=1, max=10)
     */
    private ?int $numeroLigne = null;

    /**
     * @ORM\Column(type="string", length=50, name="ns_pneu")
     */
    private ?string $numeroSerie = null;

    /**
     * @ORM\Column(type="string", length=30, name="cote_dim")
     */
    private ?string $coteDim = null;

    /**
     * @ORM\Column(type="string", length=50, name="position_machine")
     */
    private ?string $positionMachine = null;

    /**
     * @ORM\Column(type="string", length=100, name="motif_chantier")
     */
    private ?string $motifChantier = null;

    /**
     * @ORM\Column(type="string", length=20, name="diagnostic", nullable=true)
     */
    private ?string $diagnostic = null;

    /**
     * @ORM\Column(type="text", name="observation_atelier", nullable=true)
     */
    private ?string $observationAtelier = null;

    /**
     * @ORM\Column(type="datetime", name="date_diagnostic", nullable=true)
     */
    private ?DateTime $dateDiagnostic = null;

    // ----- Getters et Setters -----

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemande(): ?DemandeDiagnosticPneu
    {
        return $this->demande;
    }

    public function setDemande(?DemandeDiagnosticPneu $demande): self
    {
        $this->demande = $demande;
        return $this;
    }

    public function getNumeroLigne(): ?int
    {
        return $this->numeroLigne;
    }

    public function setNumeroLigne(int $numeroLigne): self
    {
        $this->numeroLigne = $numeroLigne;
        return $this;
    }

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(string $numeroSerie): self
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    public function getCoteDim(): ?string
    {
        return $this->coteDim;
    }

    public function setCoteDim(string $coteDim): self
    {
        $this->coteDim = $coteDim;
        return $this;
    }

    public function getPositionMachine(): ?string
    {
        return $this->positionMachine;
    }

    public function setPositionMachine(string $positionMachine): self
    {
        $this->positionMachine = $positionMachine;
        return $this;
    }

    public function getMotifChantier(): ?string
    {
        return $this->motifChantier;
    }

    public function setMotifChantier(string $motifChantier): self
    {
        $this->motifChantier = $motifChantier;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(?string $diagnostic): self
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getObservationAtelier(): ?string
    {
        return $this->observationAtelier;
    }

    public function setObservationAtelier(?string $observationAtelier): self
    {
        $this->observationAtelier = $observationAtelier;
        return $this;
    }

    public function getDateDiagnostic(): ?DateTime
    {
        return $this->dateDiagnostic;
    }

    public function setDateDiagnostic(?DateTime $dateDiagnostic): self
    {
        $this->dateDiagnostic = $dateDiagnostic;
        return $this;
    }
}
