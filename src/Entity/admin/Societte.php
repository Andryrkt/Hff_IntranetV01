<?php

namespace App\Entity\admin;

use App\Entity\TypeReparation;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use Doctrine\Common\Collections\Collection;
use App\Repository\admin\SocietteRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=SocietteRepository::class)
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
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="societe")
     */
    private $demandeInterventions;
    /**
     * @ORM\OneToMany(targetEntity=DitOrsSoumisAValidation::class, mappedBy="societe")
     */
    private $ditOrsSoumissionsAValidations;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="societtes", orphanRemoval=true)
     */
    private $users;

    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
        $this->ditOrsSoumissionsAValidations = new ArrayCollection();
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
            $demandeIntervention->setSociete($this);
        }
        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getSociete() === $this) {
                $demandeIntervention->setSociete(null);
            }
        }
        return $this;
    }

    public function setDemandeIntervention($demandeIntervention): self
    {
        $this->demandeInterventions = $demandeIntervention;
        return $this;
    }


    public function getDitOrsSoumissionsAValidations()
    {
        return $this->ditOrsSoumissionsAValidations;
    }

    public function addDitOrsSoumisAValidation(DitOrsSoumisAValidation $ditOrsSoumisAValidation): self
    {
        if (!$this->ditOrsSoumissionsAValidations->contains($ditOrsSoumisAValidation)) {
            $this->ditOrsSoumissionsAValidations[] = $ditOrsSoumisAValidation;
            $ditOrsSoumisAValidation->setSociete($this);
        }
        return $this;
    }

    public function removeDitOrsSoumissionsAValidations(DitOrsSoumisAValidation $ditOrsSoumisAValidation): self
    {
        if ($this->ditOrsSoumissionsAValidations->contains($ditOrsSoumisAValidation)) {
            $this->ditOrsSoumissionsAValidations->removeElement($ditOrsSoumisAValidation);
            if ($ditOrsSoumisAValidation->getSociete() === $this) {
                $ditOrsSoumisAValidation->setSociete(null);
            }
        }
        return $this;
    }

    public function setDitOrsSoumissionsAValidations($ditOrsSoumissionsAValidations): self
    {
        $this->ditOrsSoumissionsAValidations = $ditOrsSoumissionsAValidations;
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setSociettes($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getSociettes() === $this) {
                $user->setSociettes(null);
            }
        }

        return $this;
    }

    public function setUsers($users): self
    {
        $this->users = $users;

        return $this;
    }
}
