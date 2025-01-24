<?php

namespace App\Entity\admin;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="secteur")
 * @ORM\HasLifecycleCallbacks
 */
class Secteur
{
    use DateTrait;
/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $nom;

     /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="secteur")
     */
    private $demandeInterventions;



    public function __construct()
    {
        
        $this->demandeInterventions = new ArrayCollection();
        
    }
    /**
     * Get the value of id
     */ 

    public function getId()
    {
        return $this->id;
    }


    
    public function getNom()
    {
        return $this->nom;
    }

    
    public function setNom($nom): self
    {
        $this->nom = $nom;

        return $this;
    }
    

    /**
     * Get the value of demandeIntervention
     */ 
    public function getDemandeIntervention()
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setSecteur($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getSecteur() === $this) {
                $demandeIntervention->setSecteur(null);
            }
        }
        
        return $this;
    }
    
    public function setDemandeIntervention($demandeIntervention)
    {
        $this->demandeInterventions = $demandeIntervention;

        return $this;
    }

    
}