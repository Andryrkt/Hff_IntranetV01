<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TkiSousCategorieRepository::class)
 */
class TkiSousCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class, inversedBy="sousCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    /**
     * @ORM\ManyToMany(targetEntity=TkiAutresCategorie::class, mappedBy="sousCategories")
     */
    /**
     * @ORM\ManyToMany(targetEntity="DemandeSupportInformatique", mappedBy="autresCategories")
     */
    private $demandes;
    // ... getters et setters pour demandes
    private $autresCategories;

    public function __construct()
    {
        $this->autresCategories = new ArrayCollection();
    }

    // ... getters et setters

    
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tkiCategorie;
    
    // ... getters et setters

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreation;

    // ... (getters et setters)

    /**
     * Get the value of tkiCategorie
     */ 
    public function getTkiCategorie()
    {
        return $this->tkiCategorie;
    }

    /**
     * Set the value of tkiCategorie
     *
     * @return  self
     */ 
    public function setTkiCategorie($tkiCategorie)
    {
        $this->tkiCategorie = $tkiCategorie;

        return $this;
    }

    /**
     * Get the value of autresCategories
     */ 
    public function getAutresCategories()
    {
        return $this->autresCategories;
    }

    /**
     * Set the value of autresCategories
     *
     * @return  self
     */ 
    public function setAutresCategories($autresCategories)
    {
        $this->autresCategories = $autresCategories;

        return $this;
    }

    /**
     * Get the value of demandes
     */ 
    public function getDemandes()
    {
        return $this->demandes;
    }

    /**
     * Set the value of demandes
     *
     * @return  self
     */ 
    public function setDemandes($demandes)
    {
        $this->demandes = $demandes;

        return $this;
    }
}