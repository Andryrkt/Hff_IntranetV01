<?php

namespace App\Entity;

use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatutDemandeRepository")
 * @ORM\Table(name="Statut_demande")
 * @ORM\HasLifecycleCallbacks
 */
class StatutDemande
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Statut_Demande")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Application")
     */
    private $codeApp;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Statut")
     */
    private $codeStatut;

    /**
     * @ORM\Column(type="string", length=50, name="Description")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Badm", mappedBy="statutDemande")
     */
    private $badms;
  /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="idStatutDemande")
     */
    private $demandeInterventions;
    public function __construct()
    {
        $this->badms = new ArrayCollection();
        $this->demandeInterventions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCodeApp(): string
    {
        return $this->codeApp;
    }

    public function setCodeApp(string $codeApp): self
    {
        $this->codeApp = $codeApp;
        return $this;
    }

    public function getCodeStatut(): string
    {
        return $this->codeStatut;
    }

    public function setCodeStatut(string $codeStatut): self
    {
        $this->codeStatut = $codeStatut;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getBadms(): Collection
    {
        return $this->badms;
    }

    public function addBadm(Badm $badm): self
    {
        if (!$this->badms->contains($badm)) {
            $this->badms[] = $badm;
            $badm->setStatutDemande($this);
        }
        return $this;
    }

    public function removeBadm(Badm $badm): self
    {
        if ($this->badms->contains($badm)) {
            $this->badms->removeElement($badm);
            if ($badm->getStatutDemande() === $this) {
                $badm->setStatutDemande(null);
            }
        }
        return $this;
    }

    public function setBadms($badms): self
    {
        $this->badms = $badms;
        return $this;
    }

    public function __toString()
    {
        return $this->description; 
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
