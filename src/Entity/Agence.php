<?php

namespace App\Entity;


use App\Entity\User;
use App\Entity\Service;
use App\Traits\DateTrait;
use App\Entity\CasierValider;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AgenceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="agences")
 * @ORM\Entity(repositoryClass=AgenceRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Agence
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column("string", name="code_agence")
     *
     * @var string
     */
    private string  $codeAgence;

    /**
     * @ORM\Column("string", name="libelle_agence")
     *
     * @var string
     */
    private string $libelleAgence;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="agences")
     */
    private $users;


    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="agences", fetch="EAGER")
     * @ORM\JoinTable(name="agence_service")
     */
    private Collection $services;


    /**
     * @ORM\OneToMany(targetEntity=CasierValider::class, mappedBy="agenceRattacher")
     */
    private $casiers;

    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="agenceEmetteurId")
     */
    private $ditAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="agenceDebiteurId")
     */
    private $ditAgenceDebiteur;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->casiers = new ArrayCollection();
        $this->ditAgenceEmetteur = new ArrayCollection();
        $this->ditAgenceDebiteur = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }


    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    public function setCodeAgence($codeAgence): self
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

 
    public function getLibelleAgence()
    {
        return $this->libelleAgence;
    }

    public function setLibelleAgence(string $libelleAgence): self
    {
        $this->libelleAgence = $libelleAgence;

        return $this;
    }

   
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
        }

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
            $user->setAgences($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getAgences() === $this) {
                $user->setAgences(null);
            }
        }
        
        return $this;
    }

    public function setUsers($users): self
    {
        $this->users = $users;

        return $this;
    }


    /**
     * Get the value of demandeInterventions
     */ 
    public function getCasiers()
    {
        return $this->casiers;
    }

    public function addCasier(Casier $casier): self
    {
        if (!$this->casiers->contains($casier)) {
            $this->casiers[] = $casier;
            $casier->setAgenceRattacher($this);
        }

        return $this;
    }

    public function removeCasier(Casier $casier): self
    {
        if ($this->casiers->contains($casier)) {
            $this->casiers->removeElement($casier);
            if ($casier->getAgenceRattacher() === $this) {
                $casier->setAgenceRattacher(null);
            }
        }
        
        return $this;
    }
    
    public function setCasiers($casier)
    {
        $this->casiers = $casier;

        return $this;
    }


     /**
     * Get the value of demandeInterventions
     */ 
    public function getDitAgenceEmetteurs()
    {
        return $this->ditAgenceEmetteur;
    }

    public function addDitAgenceEmetteur(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->ditAgenceEmetteur->contains($demandeIntervention)) {
            $this->ditAgenceEmetteur[] = $demandeIntervention;
            $demandeIntervention->setAgenceEmetteurId($this);
        }

        return $this;
    }

    public function removeDitAgenceEmetteur(DemandeIntervention $ditAgenceEmetteur): self
    {
        if ($this->ditAgenceEmetteur->contains($ditAgenceEmetteur)) {
            $this->ditAgenceEmetteur->removeElement($ditAgenceEmetteur);
            if ($ditAgenceEmetteur->getAgenceEmetteurId() === $this) {
                $ditAgenceEmetteur->setAgenceEmetteurId(null);
            }
        }
        
        return $this;
    }
    public function setDitAgenceEmetteurs($ditAgenceEmetteur)
    {
        $this->ditAgenceEmetteur = $ditAgenceEmetteur;

        return $this;
    }
    


     /**
     * Get the value of demandeInterventions
     */ 
    public function getDitAgenceDebiteurs()
    {
        return $this->ditAgenceDebiteur;
    }

    public function addDitAgenceDebiteurs(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->ditAgenceDebiteur->contains($demandeIntervention)) {
            $this->ditAgenceDebiteur[] = $demandeIntervention;
            $demandeIntervention->setAgenceDebiteurId($this);
        }

        return $this;
    }

    public function removeDitAgenceDebiteur(DemandeIntervention $ditAgenceDebiteur): self
    {
        if ($this->ditAgenceDebiteur->contains($ditAgenceDebiteur)) {
            $this->ditAgenceDebiteur->removeElement($ditAgenceDebiteur);
            if ($ditAgenceDebiteur->getAgenceDebiteurId() === $this) {
                $ditAgenceDebiteur->setAgenceDebiteurId(null);
            }
        }
        
        return $this;
    }

    public function setDitAgenceDebiteurs($ditAgenceDebiteur)
    {
        $this->ditAgenceDebiteur = $ditAgenceDebiteur;

        return $this;
    }

    public function __toString()
    {
        return $this->codeAgence . $this->libelleAgence;
    }
}
