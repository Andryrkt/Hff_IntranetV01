<?php

namespace App\Entity\ddd;

use App\Repository\ddd\ChantierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChantierRepository::class)
 * @ORM\Table(name="chantier")
 */
class Chantier
{
    /**
     * @ORM\OneToMany(
     *     targetEntity=DemandeDiagnosticPneu::class,
     *     mappedBy="chantier"
     * )
     */
    private Collection $demandesDiagnosticPneu;

    public function __construct()
    {
        $this->demandesDiagnosticPneu = new ArrayCollection();
    }

    /**
     * @return Collection|DemandeDiagnosticPneu[]
     */
    public function getDemandesDiagnosticPneu(): Collection
    {
        return $this->demandesDiagnosticPneu;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id_chantier")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=20, name="code_chantier")
     */
    private ?string $codeChantier = null;

    /**
     * @ORM\Column(type="string", length=255, name="nom_chantier")
     */
    private ?string $nomChantier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCodeChantier(): ?string
    {
        return $this->codeChantier;
    }

    public function setCodeChantier(string $codeChantier): self
    {
        $this->codeChantier = $codeChantier;

        return $this;
    }

    public function getNomChantier(): ?string
    {
        return $this->nomChantier;
    }

    public function setNomChantier(string $nomChantier): self
    {
        $this->nomChantier = $nomChantier;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nomChantier ?? '';
    }
}
