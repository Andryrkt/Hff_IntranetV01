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

    public const ID_ATELIER = 3;
    public const ID_APPRO = 16;
    public const ID_RH = 12;

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
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="serviceEmetteurId")
     */
    private $ditServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="serviceDebiteurId")
     */
    private $ditServiceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="serviceEmetteurId")
     */
    private $badmServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="serviceDebiteurId")
     */
    private $badmServiceDebiteur;

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

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="serviceEmetteurId")
     */
    private Collection $tkiServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="serviceDebiteurId")
     */
    private Collection $tkiServiceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity=Mutation::class, mappedBy="serviceEmetteur")
     */
    private $mutationServiceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Mutation::class, mappedBy="serviceDebiteur")
     */
    private $mutationServiceDebiteur;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    public function __construct()
    {
        $this->agences = new ArrayCollection();
        $this->ditServiceEmetteur = new ArrayCollection();
        $this->ditServiceDebiteur = new ArrayCollection();
        $this->badmServiceEmetteur = new ArrayCollection();
        $this->badmServiceDebiteur = new ArrayCollection();
        $this->userServiceAutoriser = new ArrayCollection();
        $this->domServiceEmetteur = new ArrayCollection();
        $this->domServiceDebiteur = new ArrayCollection();
        $this->tkiServiceEmetteur = new ArrayCollection();
        $this->tkiServiceDebiteur = new ArrayCollection();
        $this->mutationServiceEmetteur = new ArrayCollection();
        $this->mutationServiceDebiteur = new ArrayCollection();
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


    /** TKI */

    /**
     * Get the value of demandeInterventions
     */
    public function getTkiServiceEmetteur()
    {
        return $this->tkiServiceEmetteur;
    }

    public function addTkiServiceEmetteur(DemandeSupportInformatique $tkiServiceEmetteur): self
    {
        if (!$this->tkiServiceEmetteur->contains($tkiServiceEmetteur)) {
            $this->tkiServiceEmetteur[] = $tkiServiceEmetteur;
            $tkiServiceEmetteur->setServiceEmetteurId($this);
        }

        return $this;
    }

    public function removeTkiServiceEmetteur(DemandeSupportInformatique $tkiServiceEmetteur): self
    {
        if ($this->tkiServiceEmetteur->contains($tkiServiceEmetteur)) {
            $this->tkiServiceEmetteur->removeElement($tkiServiceEmetteur);
            if ($tkiServiceEmetteur->getServiceEmetteurId() === $this) {
                $tkiServiceEmetteur->setServiceEmetteurId(null);
            }
        }

        return $this;
    }




    /**
     * Get the value of demandeInterventions
     */
    public function getTkiServiceDebiteur()
    {
        return $this->tkiServiceDebiteur;
    }

    public function addTkiServiceDebiteur(DemandeSupportInformatique $tkiServiceDebiteur): self
    {
        if (!$this->tkiServiceDebiteur->contains($tkiServiceDebiteur)) {
            $this->tkiServiceDebiteur[] = $tkiServiceDebiteur;
            $tkiServiceDebiteur->setServiceDebiteurId($this);
        }

        return $this;
    }

    public function removeTkiServiceDebiteur(DemandeSupportInformatique $tkiServiceDebiteur): self
    {
        if ($this->tkiServiceDebiteur->contains($tkiServiceDebiteur)) {
            $this->tkiServiceDebiteur->removeElement($tkiServiceDebiteur);
            if ($tkiServiceDebiteur->getServiceDebiteurId() === $this) {
                $tkiServiceDebiteur->setServiceDebiteurId(null);
            }
        }

        return $this;
    }

    /** MUTATION */
    /**
     * Get the value of mutationServiceEmetteur
     */
    public function getMutationServiceEmetteur()
    {
        return $this->mutationServiceEmetteur;
    }

    public function addMutationServiceEmetteur(Mutation $mutation): self
    {
        if (!$this->mutationServiceEmetteur->contains($mutation)) {
            $this->mutationServiceEmetteur[] = $mutation;
            $mutation->setServiceEmetteur($this);
        }

        return $this;
    }

    public function removeMutationServiceEmetteur(Mutation $mutation): self
    {
        if ($this->mutationServiceEmetteur->contains($mutation)) {
            $this->mutationServiceEmetteur->removeElement($mutation);
            if ($mutation->getServiceEmetteur() === $this) {
                $mutation->setServiceEmetteur(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of mutationServiceEmetteur
     *
     * @return  self
     */
    public function setMutationServiceEmetteur($mutationServiceEmetteur)
    {
        $this->mutationServiceEmetteur = $mutationServiceEmetteur;

        return $this;
    }

    /**
     * Get the value of mutationServiceDebiteur
     */
    public function getMutationServiceDebiteur()
    {
        return $this->mutationServiceDebiteur;
    }

    public function addMutationServiceDebiteur(Mutation $mutation): self
    {
        if (!$this->mutationServiceDebiteur->contains($mutation)) {
            $this->mutationServiceDebiteur[] = $mutation;
            $mutation->setServiceDebiteur($this);
        }

        return $this;
    }

    public function removeMutationServiceDebiteur(Mutation $mutation): self
    {
        if ($this->mutationServiceDebiteur->contains($mutation)) {
            $this->mutationServiceDebiteur->removeElement($mutation);
            if ($mutation->getServiceDebiteur() === $this) {
                $mutation->setServiceDebiteur(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of mutationServiceDebiteur
     *
     * @return  self
     */
    public function setMutationServiceDebiteur($mutationServiceDebiteur)
    {
        $this->mutationServiceDebiteur = $mutationServiceDebiteur;

        return $this;
    }
}
