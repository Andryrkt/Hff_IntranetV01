<?php

namespace App\Entity\admin\dit;


use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="wor_niveau_urgence")
 * @ORM\HasLifecycleCallbacks
 */
class WorNiveauUrgence{
    use DateTrait;
/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

     /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="idNiveauUrgence")
     */
    private $demandeInterventions;

    /**
     * @ORM\Column(type="string", length=50,)
     */
    private string $description;

    

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

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

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
            $demandeIntervention->setIdNiveauUrgence($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getIdNiveauUrgence() === $this) {
                $demandeIntervention->setIdNiveauUrgence(null);
            }
        }
        
        return $this;
    }
    public function setDemandeIntervention($demandeIntervention)
    {
        $this->demandeInterventions = $demandeIntervention;

        return $this;
    }

    public function __toString()
    {
        return $this->description; 
    }
}