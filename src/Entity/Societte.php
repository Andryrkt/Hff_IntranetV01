<?php

namespace App\Entity;

use App\Traits\DateTrait;
use App\Entity\TypeReparation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SocietteRepository")
 * @ORM\Table(name="societe")
 * @ORM\HasLifecycleCallbacks
 */
class Societte
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_societe")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Badm", mappedBy="statutDemande")
     */
    private $demandeInterventions;


    /**
     * @ORM\ManyToMany(targetEntity=TypeReparation::class, inversedBy="societtes")
     * @ORM\JoinTable(name="role_permissions")
     */
    private $typeReparations;

    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
        
        $this->typeReparations = new ArrayCollection();
        
    }

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

    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    
    public function setCodeSociete($codeSociete): self
    {
        $this->codeSociete = $codeSociete;

        return $this;
    }
    
    public function getDemandeInterventions()
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setCodeSociete($this);
        }
        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getCodeSociete() === $this) {
                $demandeIntervention->setCodeSociete(null);
            }
        }
        return $this;
    }

    public function setBadms($demandeIntervention): self
    {
        $this->demandeInterventions = $demandeIntervention;
        return $this;
    }


    public function getTypeReparations(): Collection
    {
        return $this->typeReparations;
    }

    public function addTypeReparation(TypeReparation $typeReparation): self
    {
        if (!$this->typeReparations->contains($typeReparation)) {
            $this->typeReparations[] = $typeReparation;
        }

        return $this;
    }

    public function removeTypeReparation(TypeReparation $typeReparation): self
    {
        if ($this->typeReparations->contains($typeReparation)) {
            $this->typeReparations->removeElement($typeReparation);
        }

        return $this;
    }

}
