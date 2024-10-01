<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TkiPlanningRepository::class)
 */
class TkiPlanning
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    /**
     * @ORM\OneToOne(targetEntity="DemandeSupportInformatique", inversedBy="planning")
     */
    private $demande;

    // ... (getters et setters)
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=11, nullable=false)
     */
    private $numeroTicket;

    /**
     * @ORM\Column(type="date")
     */
    private $datePlanning;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $heureDebutPlanning;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $heureFinPlanning;

    // ... (getters et setters)

    /**
     * Get the value of demande
     */ 
    public function getDemande()
    {
        return $this->demande;
    }

    /**
     * Set the value of demande
     *
     * @return  self
     */ 
    public function setDemande($demande)
    {
        $this->demande = $demande;

        return $this;
    }
}