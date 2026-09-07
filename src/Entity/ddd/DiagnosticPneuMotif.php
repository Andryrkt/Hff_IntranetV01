<?php

namespace App\Entity\ddd;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\diagnostic\DemandeDiagnosticPneuMotifRepository;

/**
 * @ORM\Entity(repositoryClass=DemandeDiagnosticPneuMotifRepository::class)
 * @ORM\Table(name="demande_diagnostic_pneu_motif")
 */
class DiagnosticPneuMotif
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=DemandeDiagnosticPneu::class)
     * @ORM\JoinColumn(
     *     name="id_demande",
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private ?DiagnosticPneuMotif $demande = null;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50)
     */
    private ?string $motif = null;

    public function getDemande(): ?DiagnosticPneuMotif
    {
        return $this->demande;
    }

    public function setDemande(?DiagnosticPneuMotif $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }
}
