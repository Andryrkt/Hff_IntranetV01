<?php

namespace App\Entity;

use App\Entity\Role;
use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\Societte;
use App\Traits\DateTrait;
use App\Entity\Application;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

   

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @var [type]
     */
    private $nom_utilisateur = '';

    /**
     * @ORM\Column(type="integer")
     *
     * @var [type]
     */ 
    private $matricule;

    /**
     * @ORM\Column(type="string")
     *
     * @var [type]
     */
    private $mail;
    
     /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="users")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;


     /**
     * @ORM\ManyToOne(targetEntity="Agence", inversedBy="users")
     * @ORM\JoinColumn(name="agence_id", referencedColumnName="id")
     */
    private $agences;


     /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="users")
     * @ORM\JoinTable(name="users_applications")
     */
    private $applications;
    
     /**
     * @ORM\ManyToMany(targetEntity=Societte::class, inversedBy="users")
     * @ORM\JoinTable(name="users_societe")
     */
    private $societtes;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="users")
     * @ORM\JoinTable(name="users_service")
     */
    private $services;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->societtes = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    
    public function getId()
    {
        return $this->id;
    }

   
    public function getRole()
    {
        return $this->role;
    }

  
    public function setRole($role): self
    {
        $this->role = $role;

        return $this;
    }

    
    public function getNomutilisateur(): string
    {
        return $this->nom_utilisateur;
    }

    
    public function setNomutilisateur( string $nom_utilisateur): self
    {
        $this->nom_utilisateur = $nom_utilisateur;

        return $this;
    }

    
    public function getMatricule(): int
    {
        return $this->matricule;
    }

    
    public function setMatricule($matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    
    public function getMail()
    {
        return $this->mail;
    }

    
    public function setMail( $mail): self
    {
        $this->mail = $mail;

        return $this;
    }


    public function getAgences()
    {
        return $this->agences;
    }

  
    public function setAgences($agence): self
    {
        $this->agences = $agence;

        return $this;
    }
   
     /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->contains($application)) {
            $this->applications->removeElement($application);
        }

        return $this;
    }


    
    public function getSociettes(): Collection
    {
        return $this->societtes;
    }

    public function addSociette(Societte $societte): self
    {
        if (!$this->societtes->contains($societte)) {
            $this->societtes[] = $societte;
        }

        return $this;
    }

    public function removeSociette(Societte $societte): self
    {
        if ($this->societtes->contains($societte)) {
            $this->societtes->removeElement($societte);
        }

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
}