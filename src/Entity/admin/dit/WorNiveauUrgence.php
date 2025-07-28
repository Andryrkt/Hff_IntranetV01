<?php

namespace App\Entity\admin\dit;

use App\Entity\dit\DemandeIntervention;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\Traits\DateTrait;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WorNiveauUrgenceRepository::class)
 * @ORM\Table(name="wor_niveau_urgence")
 * @ORM\HasLifecycleCallbacks
 */
class WorNiveauUrgence
{
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

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="categorie")
     */
    private Collection $supportInfo;

    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
        $this->supportInfo = new ArrayCollection();
    }

    /**=====================================================================================
    *
    * GETTERS and SETTERS
    *
    =====================================================================================*/

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
        if (! $this->demandeInterventions->contains($demandeIntervention)) {
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

    public function getSupportInfo(): Collection
    {
        return $this->supportInfo;
    }

    public function addSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if (! $this->supportInfo->contains($supportInfo)) {
            $this->supportInfo[] = $supportInfo;
            $supportInfo->setNiveauUrgence($this);
        }

        return $this;
    }

    public function removeSupportInfo(?DemandeSupportInformatique $supportInfo): self
    {
        if ($this->supportInfo->contains($supportInfo)) {
            $this->supportInfo->removeElement($supportInfo);
            if ($supportInfo->getNiveauUrgence() === $this) {
                $supportInfo->setNiveauUrgence(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->description ?? 'N/A';
    }
}
