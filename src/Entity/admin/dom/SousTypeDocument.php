<?php

namespace App\Entity\admin\dom;

use App\Entity\dom\Dom;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\mutation\Mutation;
use App\Entity\admin\dom\Indemnite;
use App\Repository\admin\dom\SousTypeDocumentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Sous_type_document")
 * @ORM\Entity(repositoryClass=SousTypeDocumentRepository::class)
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
     * @ORM\OneToMany(targetEntity=Catg::class, mappedBy="sousTypeDocument", cascade={"persist"})
     */
    private $catg;

    /**
     * @ORM\OneToMany(targetEntity=Indemnite::class, mappedBy="sousTypeDoc")
     */
    private $indemnites;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="sousTypeDocument")
     */
    private $doms;



    public function __construct()
    {
        $this->catg = new ArrayCollection();
        $this->indemnites = new ArrayCollection();
        $this->doms = new ArrayCollection();
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


    public function getDoms()
    {
        return $this->doms;
    }

    public function addDom(Dom $doms): self
    {
        if (!$this->doms->contains($doms)) {
            $this->doms[] = $doms;
            $doms->setSousTypeDocument($this);
        }

        return $this;
    }

    public function removeDom(Dom $doms): self
    {
        if ($this->doms->contains($doms)) {
            $this->doms->removeElement($doms);
            if ($doms->getSousTypeDocument() === $this) {
                $doms->setSousTypeDocument(null);
            }
        }

        return $this;
    }

    public function setDoms($doms)
    {
        $this->doms = $doms;

        return $this;
    }

    public function __toString()
    {
        return $this->codeSousType;
    }
}
