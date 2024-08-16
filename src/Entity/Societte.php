<?php

namespace App\Entity;

use App\Entity\User;
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
     * @ORM\Column(type="integer")
     */
    private int $id;

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
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="societtes")
     */
    private $users;
   

    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
        $this->users = new ArrayCollection();

    }

    public function getId(): int
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

    
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addSociette($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeSociette($this);
        }
        return $this;
    }
}
