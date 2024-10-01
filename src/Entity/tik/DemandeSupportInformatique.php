<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeSupportInformatiqueRepository::class)
 */
class DemandeSupportInformatique
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class, inversedBy="demandes")
     */
    /**
     * @ORM\ManyToMany(targetEntity="TkiSousCategorie", inversedBy="demandes")
     * @ORM\JoinTable(name="demandes_sous_categories")
     */
    /**
     * @ORM\ManyToMany(targetEntity="TkiAutresCategorie", inversedBy="demandes")
     * @ORM\JoinTable(name="demandes_autres_categories")
     */
    /**
     * @ORM\OneToOne(targetEntity="TkiPlanning", mappedBy="demande", cascade={"persist", "remove"})
     */
    private $planning;

    // ... (getters et setters)
    private $autresCategories;
    private $categorie;

    // ... getters et setters
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $numeroTicket;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $utilisateurDemandeur;

    // ... (les autres colonnes)

    /**
     * @ORM\ManyToOne(targetEntity=TkiCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tkiCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiSousCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tkiSousCategorie;

    /**
     * @ORM\ManyToOne(targetEntity=TkiAutresCategorie::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tkiAutresCategorie;

    // ... (les autres relations)

    // ... (getters et setters)

    /**
     * Get the value of categorie
     */ 
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set the value of categorie
     *
     * @return  self
     */ 
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

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
     * Get the value of planning
     */ 
    public function getPlanning()
    {
        return $this->planning;
    }

    /**
     * Set the value of planning
     *
     * @return  self
     */ 
    public function setPlanning($planning)
    {
        $this->planning = $planning;

        return $this;
    }
}