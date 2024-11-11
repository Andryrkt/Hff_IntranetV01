<?php

namespace App\Entity\admin\tik;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\tik\TkiSousCategorie;
use Doctrine\Common\Collections\Collection;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="TKI_Autres_Categorie")
 */
class TkiAutresCategorie
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Autres_Categorie")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $description;

    /**
     * @ORM\ManyToMany(targetEntity=TkiSousCategorie::class, mappedBy="autresCategories")
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

    /**
     * @return Collection
     */ 
    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategories(TkiSousCategorie $sousCategories): self
    {
        if(!$this->sousCategories->contains($sousCategories)){
            $this->sousCategories[] = $sousCategories;
            $sousCategories->addAutresCategories($this);
        }
        return $this;
    }

    public function removeSousCategories(TkiSousCategorie $sousCategories): self
    {
        if($this->sousCategories->contains($sousCategories)) {
            $this->sousCategories->removeElement($sousCategories);
            $sousCategories->removeAutresCategories($this);
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
            $supportInfo->setAutresCategorie($this);
        }

        return $this;
    }

    public function removeSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if ($this->supportInfo->contains($supportInfo)) {
            $this->supportInfo->removeElement($supportInfo);
            if ($supportInfo->getAutresCategorie() === $this) {
                $supportInfo->setAutresCategorie(null);
            }
        }
        
        return $this;
    }
}
