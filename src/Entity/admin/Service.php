<?php

namespace App\Entity\admin;


use App\Entity\dom\Dom;
use App\Entity\badm\Badm;
use App\Entity\admin\Agence;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\mutation\Mutation;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Repository\admin\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\tik\DemandeSupportInformatique;
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
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="serviceEmetteurId")
     */
    private $domServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="serviceDebiteurId")
     */
    private $domServiceDebiteur;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="serviceAutoriser")
     */
    private $userServiceAutoriser;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    public function __construct()
    {
        $this->agences = new ArrayCollection();
        $this->userServiceAutoriser = new ArrayCollection();
        $this->domServiceEmetteur = new ArrayCollection();
        $this->domServiceDebiteur = new ArrayCollection();
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
        if (!$this->agences->contains($agence)) {
            $this->agences[] = $agence;
            $agence->addService($this);
        }
        return $this;
    }

    public function removeAgence(Agence $agence): self
    {
        if ($this->agences->contains($agence)) {
            $this->agences->removeElement($agence);
            $agence->removeService($this);
        }
        return $this;
    }





    public function getUserServiceAutoriser(): Collection
    {
        return $this->userServiceAutoriser;
    }

    public function addUserServiceAutoriser(User $userServiceAutoriser): self
    {
        if (!$this->userServiceAutoriser->contains($userServiceAutoriser)) {
            $this->userServiceAutoriser[] = $userServiceAutoriser;
            $userServiceAutoriser->addServiceAutoriser($this);
        }
        return $this;
    }

    public function removeUserServiceAutoriser(User $userServiceAutoriser): self
    {
        if ($this->userServiceAutoriser->contains($userServiceAutoriser)) {
            $this->userServiceAutoriser->removeElement($userServiceAutoriser);
            $userServiceAutoriser->removeServiceAutoriser($this);
        }
        return $this;
    }


    /** DOM */


    public function getDomServiceEmetteurs()
    {
        return $this->domServiceEmetteur;
    }

    public function addDomServiceEmetteur(Dom $domServiceEmetteur): self
    {
        if (!$this->domServiceEmetteur->contains($domServiceEmetteur)) {
            $this->domServiceEmetteur[] = $domServiceEmetteur;
            $domServiceEmetteur->setServiceEmetteurId($this);
        }

        return $this;
    }

    public function removeDomServiceEmetteur(Dom $domServiceEmetteur): self
    {
        if ($this->domServiceEmetteur->contains($domServiceEmetteur)) {
            $this->domServiceEmetteur->removeElement($domServiceEmetteur);
            if ($domServiceEmetteur->getServiceEmetteurId() === $this) {
                $domServiceEmetteur->setServiceEmetteurId(null);
            }
        }

        return $this;
    }
    public function setDomServiceEmetteurs($domServiceEmetteur)
    {
        $this->domServiceEmetteur = $domServiceEmetteur;

        return $this;
    }



    /**
     * Get the value of demandeInterventions
     */
    public function getDomServiceDebiteurs()
    {
        return $this->domServiceDebiteur;
    }

    public function addDomServiceDebiteurs(Dom $domServiceDebiteur): self
    {
        if (!$this->domServiceDebiteur->contains($domServiceDebiteur)) {
            $this->domServiceDebiteur[] = $domServiceDebiteur;
            $domServiceDebiteur->setServiceDebiteurId($this);
        }

        return $this;
    }

    public function removeDomServiceDebiteur(Dom $domServiceDebiteur): self
    {
        if ($this->domServiceDebiteur->contains($domServiceDebiteur)) {
            $this->domServiceDebiteur->removeElement($domServiceDebiteur);
            if ($domServiceDebiteur->getServiceDebiteurId() === $this) {
                $domServiceDebiteur->setServiceDebiteurId(null);
            }
        }

        return $this;
    }

    public function setDomServiceDebiteurs($domServiceDebiteur)
    {
        $this->domServiceDebiteur = $domServiceDebiteur;

        return $this;
    }
}
