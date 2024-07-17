<?php

namespace App\Entity;

use App\Entity\Site;
use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CatgRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="Catg")
 * @ORM\Entity(repositoryClass=CatgRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Catg
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=SousTypeDocument::class, inversedBy="catg")
     * @ORM\JoinColumn(name="sous_type_document_id", referencedColumnName="ID_Sous_Type_Document")
     */
    private $sousTypeDocument;

    /**
     * @ORM\ManyToMany(targetEntity=Site::class, inversedBy="catgs")
     * @ORM\JoinTable(name="catg_site")
     */
    private Collection $sites;

    /**
     * @ORM\OneToMany(targetEntity=Indemnite::class, mappedBy="categories")
     */
    private $indemnites;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->indemnites = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSousTypeDocument(): ?SousTypeDocument
    {
        return $this->sousTypeDocument;
    }

    public function setSousTypeDocument(?SousTypeDocument $sousTypeDocument): self
    {
        $this->sousTypeDocument = $sousTypeDocument;
        return $this;
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): self
    {
        if (!$this->sites->contains($site)) {
            $this->sites[] = $site;
        }

        return $this;
    }

    public function removeSite(Site $site): self
    {
        if ($this->sites->contains($site)) {
            $this->sites->removeElement($site);
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
            $indemnite->setCatg($this);
        }
        return $this;
    }

    public function removeIndemnite(Indemnite $indemnite): self
    {
        if ($this->indemnites->contains($indemnite)) {
            $this->indemnites->removeElement($indemnite);
            if ($indemnite->getCatg() === $this) {
                $indemnite->setCatg(null);
            }
        }

        return $this;
    }
}
