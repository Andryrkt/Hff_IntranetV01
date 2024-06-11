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
     * @ORM\Column(type="integer", name="id_type_document")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=3, name="code_document")
     */
    private string $codeDocument;
    /**
     * @ORM\Column(type="string", length=50, name="desciption")
     */
    private string $description;
    /**
     * @ORM\Column(type="datetime",  name="date_creation")
     */
    private datetime $dateCreation;
    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="typeDocument")
     */
    private $demandeInterventions;
    public function __construct()
    {

        $this->demandeInterventions = new ArrayCollection();
    }
    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of codeDocument
     */
    public function getCodeDocument()
    {
        return $this->codeDocument;
    }

    /**
     * Set the value of codeDocument
     *
     * @return  self
     */
    public function setCodeDocument($codeDocument)
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
    public function setDemandeInterventions($demandeInterventions)
    {
        $this->demandeInterventions = $demandeInterventions;

        return $this;
    }
}
