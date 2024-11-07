<?php

namespace App\Entity\admin\tik;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_CATEGORIE")
 */
class TkiCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Categorie")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $description;


    /**
     * @ORM\OneToMany(targetEntity=TKISousCategorie::class, mappedBy="categorie")
     */
    private Collection $sousCategories;

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


    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategories(?TkiSousCategorie $sousCategories): self
    {
        if (!$this->sousCategories->contains($sousCategories)) {
            $this->sousCategories[] = $sousCategories;
            $sousCategories->setCategorie($this);
        }

        return $this;
    }

    public function removeSousCategories(?TkiSousCategorie $sousCategories): self
    {
        if ($this->sousCategories->contains($sousCategories)) {
            $this->sousCategories->removeElement($sousCategories);
            if ($sousCategories->getCategorie() === $this) {
                $sousCategories->setCategorie(null);
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
?>