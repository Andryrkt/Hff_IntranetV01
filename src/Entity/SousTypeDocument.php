<?php

namespace App\Entity;

use App\Entity\Indemnite;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Sous_type_document")
 * @ORM\HasLifecycleCallbacks
 */
class SousTypeDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Sous_Type_Document")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=4, name="Code_Document")
     */
    private $codeDocument;

    /**
     * @ORM\Column(type="string", length=4, name="Code_Sous_Type", nullable=true)
     */
    private $codeSousType;

    /**
     * @ORM\Column(type="string", length=50, name="Description", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", name="Date_creation")
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity=Catg::class, mappedBy="sousTypeDocument")
     */
    private $catg;

    /**
     * @ORM\OneToMany(targetEntity=Indemnite::class, mappedBy="sousTypeDoc")
     */
    private $indemnites;

    public function __construct()
    {
        $this->catg = new ArrayCollection();
        $this->indemnites = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCodeDocument(): string
    {
        return $this->codeDocument;
    }

    public function setCodeDocument(string $codeDocument): self
    {
        $this->codeDocument = $codeDocument;
        return $this;
    }

    public function getCodeSousType(): ?string
    {
        return $this->codeSousType;
    }

    public function setCodeSousType(?string $codeSousType): self
    {
        $this->codeSousType = $codeSousType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * @return Collection|Catg[]
     */
    public function getCatg(): Collection
    {
        return $this->catg;
    }

    public function addCatg(Catg $catg): self
    {
        if (!$this->catg->contains($catg)) {
            $this->catg[] = $catg;
            $catg->setSousTypeDocument($this);
        }
        return $this;
    }

    public function removeCatg(Catg $catg): self
    {
        if ($this->catg->contains($catg)) {
            $this->catg->removeElement($catg);
            if ($catg->getSousTypeDocument() === $this) {
                $catg->setSousTypeDocument(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Indemnite[]
     */
    public function getIndemnites(): Collection
    {
        return $this->indemnites;
    }

    public function addIndemnite(Indemnite $indemnite): self
    {
        if (!$this->indemnites->contains($indemnite)) {
            $this->indemnites[] = $indemnite;
            $indemnite->setSousTypeDoc($this);
        }
        return $this;
    }

    public function removeIndemnite(Indemnite $indemnite): self
    {
        if ($this->indemnites->contains($indemnite)) {
            $this->indemnites->removeElement($indemnite);
            if ($indemnite->getSousTypeDoc() === $this) {
                $indemnite->setSousTypeDoc(null);
            }
        }

        return $this;
    }
}