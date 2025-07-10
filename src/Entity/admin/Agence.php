<?php

namespace App\Entity\admin;


use App\Entity\dom\Dom;
use App\Entity\badm\Badm;
use App\Entity\cas\Casier;
use App\Entity\admin\Service;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\cas\CasierValider;
use App\Entity\mutation\Mutation;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Repository\admin\AgenceRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;

/**
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
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="agences", fetch="EAGER")
     * @ORM\JoinTable(name="agence_service")
     */
    private Collection $services;


    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="agenceEmetteurId")
     */
    private $domAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="agenceDebiteurId")
     */
    private $domAgenceDebiteur;



    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="agencesAutorisees")
     * @ORM\JoinTable(name="agence_user", 
     *      joinColumns={@ORM\JoinColumn(name="agence_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $usersAutorises;


    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->usersAutorises = new ArrayCollection();
        $this->domAgenceEmetteur = new ArrayCollection();
        $this->domAgenceDebiteur = new ArrayCollection();
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


    public function getUsersAutorises(): Collection
    {
        return $this->usersAutorises;
    }

    public function addUserAutorise(User $user): self
    {
        if (!$this->usersAutorises->contains($user)) {
            $this->usersAutorises[] = $user;
            $user->addAgenceAutorise($this);
        }

        return $this;
    }

    public function removeUserAutorise(User $user): self
    {
        if ($this->usersAutorises->contains($user)) {
            $this->usersAutorises->removeElement($user);
            $user->removeAgenceAutorise($this);
        }

        return $this;
    }






    /** DOM */

    public function getDomAgenceEmetteurs()
    {
        return $this->domAgenceEmetteur;
    }

    public function addDomAgenceEmetteur(Dom $domAgenceEmetteur): self
    {
        if (!$this->domAgenceEmetteur->contains($domAgenceEmetteur)) {
            $this->domAgenceEmetteur[] = $domAgenceEmetteur;
            $domAgenceEmetteur->setAgenceEmetteurId($this);
        }

        return $this;
    }

    public function removeDomAgenceEmetteur(Dom $domAgenceEmetteur): self
    {
        if ($this->domAgenceEmetteur->contains($domAgenceEmetteur)) {
            $this->domAgenceEmetteur->removeElement($domAgenceEmetteur);
            if ($domAgenceEmetteur->getAgenceEmetteurId() === $this) {
                $domAgenceEmetteur->setAgenceEmetteurId(null);
            }
        }

        return $this;
    }

    public function setDomAgenceEmetteurs($domAgenceEmetteur)
    {
        $this->domAgenceEmetteur = $domAgenceEmetteur;

        return $this;
    }



    /**
     * Get the value of demandeInterventions
     */
    public function getDomAgenceDebiteurs()
    {
        return $this->domAgenceDebiteur;
    }

    public function addDomAgenceDebiteurs(Dom $domAgenceDebiteur): self
    {
        if (!$this->domAgenceDebiteur->contains($domAgenceDebiteur)) {
            $this->domAgenceDebiteur[] = $domAgenceDebiteur;
            $domAgenceDebiteur->setAgenceDebiteurId($this);
        }

        return $this;
    }

    public function removeDomAgenceDebiteur(Dom $domAgenceDebiteur): self
    {
        if ($this->domAgenceDebiteur->contains($domAgenceDebiteur)) {
            $this->domAgenceDebiteur->removeElement($domAgenceDebiteur);
            if ($domAgenceDebiteur->getAgenceDebiteurId() === $this) {
                $domAgenceDebiteur->setAgenceDebiteurId(null);
            }
        }

        return $this;
    }

    public function setDomAgenceDebiteurs($domAgenceDebiteur)
    {
        $this->domAgenceDebiteur = $domAgenceDebiteur;

        return $this;
    }
}
