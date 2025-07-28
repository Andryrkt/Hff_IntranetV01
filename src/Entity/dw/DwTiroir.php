<?php

namespace App\Entity\dw;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="DW_Tiroir")
 * @ORM\HasLifecycleCallbacks
 */
class DwTiroir
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100, name="id_tiroir")
     */
    private $idTiroir;

    /**
     * @ORM\Column(type="string", length=255, name="designation_tiroir")
     */
    private $designationTiroir;

    /**
     * @ORM\OneToMany(targetEntity=DwDemandeIntervention::class, mappedBy="tiroir")
     */
    private $demandesIntervention;

    /**
     * @ORM\OneToMany(targetEntity=DwCommande::class, mappedBy="tiroir")
     */
    private $commande;

    /**
     * @ORM\OneToMany(targetEntity=DwOrdreDeReparation::class, mappedBy="tiroir")
     */
    private $ordresDeReparation;

    /**
     * @ORM\OneToMany(targetEntity=DwRapportIntervention::class, mappedBy="tiroir")
     */
    private $rapportsIntervention;

    /** ===========================================================================
     * getteur and setteur
     *
     * ================================================================================
     */
    public function __construct()
    {
        $this->demandesIntervention = new ArrayCollection();
        $this->ordresDeReparation = new ArrayCollection();
        $this->rapportsIntervention = new ArrayCollection();

    }

    /**
     * Get the value of idTiroir
     */
    public function getIdTiroir()
    {
        return $this->idTiroir;
    }

    /**
     * Set the value of idTiroir
     *
     * @return  self
     */
    public function setIdTiroir($idTiroir)
    {
        $this->idTiroir = $idTiroir;

        return $this;
    }

    /**
     * Get the value of designationTiroir
     */
    public function getDesignationTiroir()
    {
        return $this->designationTiroir;
    }

    /**
     * Set the value of designationTiroir
     *
     * @return  self
     */
    public function setDesignationTiroir($designationTiroir)
    {
        $this->designationTiroir = $designationTiroir;

        return $this;
    }

    /**
       * @return Collection|DwDemandeIntervention[]
       */
    public function getDemandesIntervention(): Collection
    {
        return $this->demandesIntervention;
    }

    public function addDemandeIntervention(DwDemandeIntervention $demandeIntervention): self
    {
        if (! $this->demandesIntervention->contains($demandeIntervention)) {
            $this->demandesIntervention[] = $demandeIntervention;
            $demandeIntervention->setTiroir($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DwDemandeIntervention $demandeIntervention): self
    {
        if ($this->demandesIntervention->removeElement($demandeIntervention)) {
            // Set the owning side to null (unless already changed)
            if ($demandeIntervention->getTiroir() === $this) {
                $demandeIntervention->setTiroir(null);
            }
        }

        return $this;
    }

    /**
    * @return Collection|DwCommande[]
    */
    public function getCommande(): Collection
    {
        return $this->commande;
    }

    public function addCommande(DwCommande $commande): self
    {
        if (! $this->commande->contains($commande)) {
            $this->commande[] = $commande;
            $commande->setTiroir($this);
        }

        return $this;
    }

    public function removeCommande(DwCommande $commande): self
    {
        if ($this->commande->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getTiroir() === $this) {
                $commande->setTiroir(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DwOrdreDeReparation[]
     */
    public function getOrdresDeReparation(): Collection
    {
        return $this->ordresDeReparation;
    }

    public function addOrdreDeReparation(DwOrdreDeReparation $ordreDeReparation): self
    {
        if (! $this->ordresDeReparation->contains($ordreDeReparation)) {
            $this->ordresDeReparation[] = $ordreDeReparation;
            $ordreDeReparation->setTiroir($this);
        }

        return $this;
    }

    public function removeOrdreDeReparation(DwOrdreDeReparation $ordreDeReparation): self
    {
        if ($this->ordresDeReparation->removeElement($ordreDeReparation)) {
            // set the owning side to null (unless already changed)
            if ($ordreDeReparation->getTiroir() === $this) {
                $ordreDeReparation->setTiroir(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DwRapportIntervention[]
     */
    public function getRapportsIntervention(): Collection
    {
        return $this->rapportsIntervention;
    }

    public function addRapportIntervention(DwRapportIntervention $rapportIntervention): self
    {
        if (! $this->rapportsIntervention->contains($rapportIntervention)) {
            $this->rapportsIntervention[] = $rapportIntervention;
            $rapportIntervention->setTiroir($this);
        }

        return $this;
    }

    public function removeRapportIntervention(DwRapportIntervention $rapportIntervention): self
    {
        if ($this->rapportsIntervention->removeElement($rapportIntervention)) {
            // set the owning side to null (unless already changed)
            if ($rapportIntervention->getTiroir() === $this) {
                $rapportIntervention->setTiroir(null);
            }
        }

        return $this;
    }
}
