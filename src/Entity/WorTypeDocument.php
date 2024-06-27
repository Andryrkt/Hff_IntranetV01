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
 * @ORM\Table(name="wor_type_document")
 * @ORM\HasLifecycleCallbacks
 */
class WorTypeDocument
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    
    /**
     * @ORM\Column(type="string", length=3, name="code_document")
     */
    private string $codeDocument;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $description;

    
   /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="typeDocument")
     */
    private $demandeInterventions;

    
    public function __construct()
    {

        $this->demandeInterventions = new ArrayCollection();
    }
    
    public function getId(): int
    {
        return $this->id;
    }

    
    public function getCodeDocument()
    {
        return $this->codeDocument;
    }

  
    public function setCodeDocument($codeDocument): self
    {
        $this->codeDocument = $codeDocument;

        return $this;
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
     * Get the value of demandeInterventions
     */
    public function getDemandeInterventions()
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setTypeDocument($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getTypeDocument() === $this) {
                $demandeIntervention->setTypeDocument(null);
            }
        }

        return $this;
    }
    public function setDemandeInterventions($demandeInterventions)
    {
        $this->demandeInterventions = $demandeInterventions;

        return $this;
    }

    public function __toString()
    {
        return $this->description; 
    }
}
