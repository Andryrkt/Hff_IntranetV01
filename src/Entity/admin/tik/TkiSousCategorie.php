<?php

namespace App\Entity\admin\tik;

use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\Traits\DateTrait;
use App\Repository\admin\tik\TkiSousCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiSousCategorieRepository::class)
 * @ORM\Table(name="TKI_SOUS_CATEGORIE")
 */
class TkiSousCategorie
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=TkiCategorie::class, mappedBy="sousCategories")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity=TkiAutresCategorie::class, inversedBy="sousCategories")
     * @ORM\JoinTable(name="souscategorie_autrescategories")
     */
    private $autresCategories;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="sousCategorie")
     */
    private $supportInfo;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->autresCategories = new ArrayCollection();
        $this->supportInfo = new ArrayCollection();
    }

    /**=====================================================================================
     *
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    public function getId()
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

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(TkiCategorie $categorie): self
    {
        if (! $this->categories->contains($categorie)) {
            $this->categories[] = $categorie;
            $categorie->addSousCategorie($this);
        }

        return $this;
    }

    public function setCategories(Collection $categorie): self
    {
        $this->categories = $categorie;

        return $this;
    }

    public function removeCategorie(TkiCategorie $categorie): self
    {
        if ($this->categories->removeElement($categorie)) {
            $categorie->removeSousCategorie($this); // Maintenir la relation bidirectionnelle
        }

        return $this;
    }

    public function getAutresCategories(): Collection
    {
        return $this->autresCategories;
    }

    public function addAutresCategorie(TkiAutresCategorie $autresCategorie): self
    {
        if (! $this->autresCategories->contains($autresCategorie)) {
            $this->autresCategories[] = $autresCategorie;
            $autresCategorie->addSousCategorie($this);
        }

        return $this;
    }

    public function setAutresCategories(Collection $autresCategorie): self
    {
        $this->autresCategories = $autresCategorie;

        return $this;
    }

    public function removeAutresCategorie(TkiAutresCategorie $autresCategorie): self
    {
        if ($this->autresCategories->contains($autresCategorie)) {
            $this->autresCategories->removeElement($autresCategorie);
            $autresCategorie->removeSousCategorie($this);
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
            $supportInfo->setSousCategorie($this);
        }

        return $this;
    }

    public function removeSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if ($this->supportInfo->contains($supportInfo)) {
            $this->supportInfo->removeElement($supportInfo);
            if ($supportInfo->getSousCategorie() === $this) {
                $supportInfo->setSousCategorie(null);
            }
        }

        return $this;
    }
}
