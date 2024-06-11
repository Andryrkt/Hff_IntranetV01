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
     * @ORM\Column(type="string", length=3)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=3, name="code_societe")
     */
    private $codeSociete;

    /**
     * @ORM\Column(type="string", length=50, name="Description")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Badm", mappedBy="statutDemande")
     */
    private $demandeInterventions;

    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setStatutDemande($this);
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
}
