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
     * @ORM\ManyToMany(targetEntity=TkiCategorie::class, mappedBy="sousCategorie")
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
    private Collection $supportInfo;


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
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategories(TkiCategorie $categories): self
    {
        if(!$this->categories->contains($categories)){
            $this->categories[] = $categories;
            $categories->addSousCategories($this);
        }
        return $this;
    }

    public function removeCategories(TkiCategorie $categories): self
    {
        if($this->categories->contains($categories)) {
            $this->categories->removeElement($categories);
            $categories->removeSousCategories($this);
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAutresCategories(): Collection
    {
        return $this->autresCategories;
    }

    public function addAutresCategories(TkiAutresCategorie $autresCategories): self
    {
        if (!$this->autresCategories->contains($autresCategories)) {
            $this->autresCategories[] = $autresCategories;
        }

        return $this;
    }

    public function removeAutresCategories(TkiAutresCategorie $autresCategories): self
    {
        if ($this->autresCategories->contains($autresCategories)) {
            $this->autresCategories->removeElement($autresCategories);
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
