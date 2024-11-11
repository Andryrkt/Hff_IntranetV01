<?php

namespace App\Entity\admin\tik;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\tik\TkiCategorie;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_SOUS_CATEGORIE")
 */
class TkiSousCategorie
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Sous_Categorie")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $description;

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class, inversedBy="sousCategories")
     * @ORM\JoinColumn(nullable=false, name="ID_Categorie", referencedColumnName="idCategorie")
     */
    private ?TkiCategorie $categorie;

    /**
     * @ORM\OneToMany(targetEntity=TkiAutresCategorie::class, mappedBy="sousCategorie")
     */
    private Collection $autresCategories;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="sousCategorie")
     */
    private Collection $supportInfo;


    public function __construct()
    {
        $this->autresCategories = new ArrayCollection();
        $this->supportInfo = new ArrayCollection();
    }

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    /**
     * Get the value of id
     */ 
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

    public function getCategorie(): ?TkiCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?TkiCategorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getAutresCategories(): Collection
    {
        return $this->autresCategories;
    }

    public function addAutresCategories(?TkiAutresCategorie $autresCategories): self
    {
        if (!$this->autresCategories->contains($autresCategories)) {
            $this->autresCategories[] = $autresCategories;
            $autresCategories->setSousCategorie($this);
        }

        return $this;
    }

    public function removeAutresCategories(?TkiAutresCategorie $autresCategories): self
    {
        if ($this->autresCategories->contains($autresCategories)) {
            $this->autresCategories->removeElement($autresCategories);
            if ($autresCategories->getSousCategorie() === $this) {
                $autresCategories->setSousCategorie(null);
            }
        }
        
        return $this;
    }



    public function getSupportInfo(): Collection
    {
        return $this->supportInfo;
    }

    public function addSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if (!$this->supportInfo->contains($supportInfo)) {
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
