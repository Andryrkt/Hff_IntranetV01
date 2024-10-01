<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TkiCategorieRepository::class)
 */
class TkiCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    /**
     * @ORM\OneToMany(targetEntity=TkiSousCategorie::class, mappedBy="tkiCategorie")
     */
    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="categorie")
     */
    private $demandes;
    private $sousCategories;
    public function __construct()
    {
        $this->sousCategories = new ArrayCollection();
    }

    // ... getters et setters
    
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreation;

    // ... (getters et setters)

    /**
     * Get the value of sousCategories
     */ 
    public function getSousCategories()
    {
        return $this->sousCategories;
    }

    /**
     * Set the value of sousCategories
     *
     * @return  self
     */ 
    public function setSousCategories($sousCategories)
    {
        $this->sousCategories = $sousCategories;

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