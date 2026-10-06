<?php

namespace App\Entity\admin\dit;

use App\Entity\dit\AncienDit;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\dit\WorTypeDocumentRepository;


/**
 * @ORM\Entity(repositoryClass=WorTypeDocumentRepository::class)
 * @ORM\Table(name="wor_type_document")
 * @ORM\HasLifecycleCallbacks
 */
class WorTypeDocument
{
    use DateTrait;

    public const MAINTENANCE_CURATIVE = 6;

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
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="typeDocument")
     */
    private $demandeInterventions;

    /**
     * @ORM\OneToMany(targetEntity=AncienDit::class, mappedBy="typeDocument")
     */
    private $ancienDit;

    
    public function __construct()
    {
        $this->ancienDit = new ArrayCollection();
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
    

    /**
     * Get the value of demandeInterventions
     */
    public function getAncienDit()
    {
        return $this->ancienDit;
    }

    public function addAncienDit(AncienDit $ancienDit): self
    {
        if (!$this->ancienDit->contains($ancienDit)) {
            $this->ancienDit[] = $ancienDit;
            $ancienDit->setTypeDocument($this);
        }

        return $this;
    }

    public function removeAncienDit(AncienDit $ancienDit): self
    {
        if ($this->ancienDit->contains($ancienDit)) {
            $this->ancienDit->removeElement($ancienDit);
            if ($ancienDit->getTypeDocument() === $this) {
                $ancienDit->setTypeDocument(null);
            }
        }

        return $this;
    }
    public function setAncienDit($ancienDit)
    {
        $this->ancienDit = $ancienDit;

        return $this;
    }


    public function __toString()
    {
        return $this->description ?? 'N/A'; 
    }
}
