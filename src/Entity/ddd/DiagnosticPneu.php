<?php

namespace App\Entity\ddd;

use App\Repository\ddd\DiagnosticPneuRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\JoinColumn(nullable=false)
     */
    private ?DemandeDiagnosticPneu $demande = null;

    /**
     * @ORM\Column(type="string", length=50, name="numero_serie")
     */
    private ?string $numeroSerie = null;   // N/S pneu

    /**
     * @ORM\Column(type="string", length=50, name="cote_dim")
     */
    private ?string $coteDim = null;       // Cote / dim

    /**
     * @ORM\Column(type="string", length=50, name="position_machine")
     */
    private ?string $positionMachine = null; // Position machine

    /**
     * @ORM\Column(type="string", length=100, name="motif_chantier")
     */
    private ?string $motifChantier = null;  // Motif chantier

    // Getters and setters...

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
}
