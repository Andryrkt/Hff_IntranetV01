<?php

namespace App\Entity\admin\tik;

use App\Entity\tik\DemandeSupportInformatique;
use App\Repository\admin\tik\TkiCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiCategorieRepository::class)
 * @ORM\Table(name="TKI_CATEGORIE")
 */
class TkiCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $description;

    /**
     * @ORM\ManyToMany(targetEntity=TkiSousCategorie::class, inversedBy="categories")
     * @ORM\JoinTable(name="categorie_souscategorie")
     */
    private $sousCategories;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="categorie")
     */
    private Collection $supportInfo;

    public function __construct()
    {
        $this->sousCategories = new ArrayCollection();
        $this->supportInfo = new ArrayCollection();
    }

    /**=====================================================================================
     *
     * GETTERS and SETTERS
     *
    =====================================================================================*/

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

    /**
     * @return Collection
     */
    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategorie(TkiSousCategorie $sousCategorie): self
    {
        if (! $this->sousCategories->contains($sousCategorie)) {
            $this->sousCategories[] = $sousCategorie;
            $sousCategorie->addCategorie($this);
        }

        return $this;
    }

    public function setSousCategories(Collection $sousCategorie): self
    {
        $this->sousCategories = $sousCategorie;

        return $this;
    }

    public function removeSousCategorie(TkiSousCategorie $sousCategorie): self
    {
        if ($this->sousCategories->removeElement($sousCategorie)) {
            $sousCategorie->removeCategorie($this); // Maintient la relation bidirectionnelle
        }

        return $this;
    }

    public function getSupportInfo(): Collection
    {
        return $this->supportInfo;
    }

    public function addSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if (! $this->supportInfo->contains($supportInfo)) {
            $this->supportInfo[] = $supportInfo;
            $supportInfo->setCategorie($this);
        }

        return $this;
    }

    public function removeSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if ($this->supportInfo->contains($supportInfo)) {
            $this->supportInfo->removeElement($supportInfo);
            if ($supportInfo->getCategorie() === $this) {
                $supportInfo->setCategorie(null);
            }
        }

        return $this;
    }
}
