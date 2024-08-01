<?php

namespace App\Entity;


use App\Entity\User;
use App\Entity\Agence;
use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="services")
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Service
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column("string", name="code_service")
     *
     * @var string
     */
    private string $codeService;

    /**
     * @ORM\Column("string", name="libelle_service")
     *
     * @var string
     */
    private string $libelleService;

/**
     * @ORM\ManyToMany(targetEntity=Agence::class, mappedBy="services")
     
     */
    private Collection $agences;


    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="services")
     */
    private Collection $users;

    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="serviceEmetteurId")
     */
    private $ditServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="serviceDebiteurId")
     */
    private $ditServiceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity="Badm", mappedBy="serviceEmetteurId")
     */
    private $badmServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity="Badm", mappedBy="serviceDebiteurId")
     */
    private $badmServiceDebiteur;


    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="servicesUtilisateur")
     */
    private $userService;


    public function __construct()
    {
        $this->agences = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->ditServiceEmetteur = new ArrayCollection();
        $this->ditServiceDebiteur = new ArrayCollection();
        $this->badmServiceEmetteur = new ArrayCollection();
        $this->badmServiceDebiteur = new ArrayCollection();
        $this->userService = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }



    public function getCodeService()
    {
        return $this->codeService;
    }

  
    public function setCodeService($codeService): self
    {
        $this->codeService = $codeService;

        return $this;
    }


    public function getLibelleService()
    {
        return $this->libelleService;
    }

  
    public function setLibelleService(string $libelleService): self
    {
        $this->libelleService = $libelleService;

        return $this;
    }


    public function getAgences(): Collection
    {
        return $this->agences;
    }

    public function addAgence(Agence $agence): self
    {
        if(!$this->agences->contains($agence)){
            $this->agences[] = $agence;
            $agence->addService($this);
        }
        return $this;
    }

    public function removeAgence(Agence $agence): self
    {
        if($this->agences->contains($agence)) {
            $this->agences->removeElement($agence);
          $agence->removeService($this);
        }
        return $this;
    }


    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if(!$this->users->contains($user)){
            $this->users[] = $user;
            $user->addService($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        if($this->users->contains($user)) {
            $this->users->removeElement($user);
          $user->removeService($this);
        }
        return $this;
    }


    /** DIT */

       /**
     * Get the value of demandeInterventions
     */ 
    public function getDitServiceEmetteurs()
    {
        return $this->ditServiceEmetteur;
    }

    public function addDitServiceEmetteur(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->ditServiceEmetteur->contains($demandeIntervention)) {
            $this->ditServiceEmetteur[] = $demandeIntervention;
            $demandeIntervention->setServiceEmetteurId($this);
        }

        return $this;
    }

    public function removeDitServiceEmetteur(DemandeIntervention $ditAgenceEmetteur): self
    {
        if ($this->ditServiceEmetteur->contains($ditAgenceEmetteur)) {
            $this->ditServiceEmetteur->removeElement($ditAgenceEmetteur);
            if ($ditAgenceEmetteur->getServiceEmetteurId() === $this) {
                $ditAgenceEmetteur->setServiceEmetteurId(null);
            }
        }
        
        return $this;
    }
    public function setDitServiceEmetteurs($ditAgenceEmetteur)
    {
        $this->ditServiceEmetteur = $ditAgenceEmetteur;

        return $this;
    }
    


     /**
     * Get the value of demandeInterventions
     */ 
    public function getDitServiceDebiteurs()
    {
        return $this->ditServiceDebiteur;
    }

    public function addDitServiceDebiteurs(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->ditServiceDebiteur->contains($demandeIntervention)) {
            $this->ditServiceDebiteur[] = $demandeIntervention;
            $demandeIntervention->setServiceDebiteurId($this);
        }

        return $this;
    }

    public function removeDitServiceDebiteur(DemandeIntervention $ditAgenceDebiteur): self
    {
        if ($this->ditServiceDebiteur->contains($ditAgenceDebiteur)) {
            $this->ditServiceDebiteur->removeElement($ditAgenceDebiteur);
            if ($ditAgenceDebiteur->getServiceDebiteurId() === $this) {
                $ditAgenceDebiteur->setServiceDebiteurId(null);
            }
        }
        
        return $this;
    }
    
    public function setDitServiceDebiteurs($ditAgenceDebiteur)
    {
        $this->ditServiceDebiteur = $ditAgenceDebiteur;

        return $this;
    }

/** BADM */

       /**
     * Get the value of demandeInterventions
     */ 
    public function getbadmServiceEmetteurs()
    {
        return $this->badmServiceEmetteur;
    }

    public function addbadmServiceEmetteur(Badm $badm): self
    {
        if (!$this->badmServiceEmetteur->contains($badm)) {
            $this->badmServiceEmetteur[] = $badm;
            $badm->setServiceEmetteurId($this);
        }

        return $this;
    }

    public function removebadmServiceEmetteur(Badm $badmAgenceEmetteur): self
    {
        if ($this->badmServiceEmetteur->contains($badmAgenceEmetteur)) {
            $this->badmServiceEmetteur->removeElement($badmAgenceEmetteur);
            if ($badmAgenceEmetteur->getServiceEmetteurId() === $this) {
                $badmAgenceEmetteur->setServiceEmetteurId(null);
            }
        }
        
        return $this;
    }
    public function setbadmServiceEmetteurs($badmAgenceEmetteur)
    {
        $this->badmServiceEmetteur = $badmAgenceEmetteur;

        return $this;
    }
    


     /**
     * Get the value of demandeInterventions
     */ 
    public function getBadmServiceDebiteurs()
    {
        return $this->badmServiceDebiteur;
    }

    public function addBadmServiceDebiteurs(Badm $badm): self
    {
        if (!$this->badmServiceDebiteur->contains($badm)) {
            $this->badmServiceDebiteur[] = $badm;
            $badm->setServiceDebiteurId($this);
        }

        return $this;
    }

    public function removeBadmServiceDebiteur(Badm $badmAgenceDebiteur): self
    {
        if ($this->badmServiceDebiteur->contains($badmAgenceDebiteur)) {
            $this->badmServiceDebiteur->removeElement($badmAgenceDebiteur);
            if ($badmAgenceDebiteur->getServiceDebiteurId() === $this) {
                $badmAgenceDebiteur->setServiceDebiteurId(null);
            }
        }
        
        return $this;
    }
    
    public function setBadmServiceDebiteurs($badmAgenceDebiteur)
    {
        $this->badmServiceDebiteur = $badmAgenceDebiteur;

        return $this;
    }



      /**
     * Get the value of demandeInterventions
     */ 
    public function getUserServices()
    {
        return $this->userService;
    }

    public function addUserService(User $userService): self
    {
        if (!$this->userService->contains($userService)) {
            $this->userService[] = $userService;
            $userService->setServiceUtilisateur($this);
        }

        return $this;
    }

    public function removUserService(User $userService): self
    {
        if ($this->userService->contains($userService)) {
            $this->userService->removeElement($userService);
            if ($userService->getServiceUtilisateur() === $this) {
                $userService->setServiceUtilisateur(null);
            }
        }
        
        return $this;
    }
    
    public function setUserServices($userService)
    {
        $this->userService = $userService;

        return $this;
    }

}
