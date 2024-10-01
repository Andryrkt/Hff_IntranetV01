<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TkiAutresCategorieRepository::class)
 */
class TkiAutresCategorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    /**
     * @ORM\ManyToMany(targetEntity=TkiSousCategorie::class, inversedBy="autresCategories")
     * @ORM\JoinTable(name="sous_categories_autres_categories")
     */
     /**
     * @ORM\ManyToMany(targetEntity="DemandeSupportInformatique", mappedBy="autresCategories")
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
     * @ORM\ManyToOne(targetEntity=TkiSousCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tkiSousCategorie;

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