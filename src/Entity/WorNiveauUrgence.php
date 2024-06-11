<?php
namespace App\Entity;

use App\Entity\Role;
use App\Traits\DateTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

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
     * @ORM\Column(type="integer", name="id_niveau_urgence")
     */
    private $id;
     /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="idNiveauUrgence")
     */
    private $demandeInterventions;
    /**
     * @ORM\Column(type="string", length=50,)
     */
    private string $description;
    /**
     * @ORM\Column(type="datetime",  name="date_creation")
     */
    private datetime $dateCreation;

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
     * Get the value of dateCreation
     */ 
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set the value of dateCreation
     *
     * @return  self
     */ 
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
    

    /**
     * Get the value of demandeIntervention
     */ 
    public function getDemandeIntervention()
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(User $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setRole($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(User $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getRole() === $this) {
                $demandeIntervention->setRole(null);
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