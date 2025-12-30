<?php

namespace App\Entity\admin;


use App\Entity\dom\Dom;
use App\Entity\badm\Badm;
use App\Entity\cas\Casier;
use App\Entity\admin\Service;
use App\Entity\da\DemandeAppro;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\cas\CasierValider;
use App\Entity\mutation\Mutation;
use App\Entity\admin\AgenceService;
use App\Entity\admin\utilisateur\User;
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
     * @ORM\Column(type="string", name="code_agence")
     *
     * @var string
     */
    private string  $codeAgence;

    /**
     * @ORM\Column(type="string", name="libelle_agence")
     *
     * @var string
     */
    private string $libelleAgence;

    /**
     * @ORM\OneToMany(targetEntity=AgenceService::class, mappedBy="agence", cascade={"persist", "remove"})
     */
    private Collection $agenceServices;

    /**
     * @ORM\OneToMany(targetEntity=CasierValider::class, mappedBy="agenceRattacher")
     */
    private $casiers;

    /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="agenceEmetteurId")
     */
    private $ditAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="agenceDebiteurId")
     */
    private $ditAgenceDebiteur;


    /**
     * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="agenceEmetteurId")
     */
    private $badmAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="agenceDebiteurId")
     */
    private $badmAgenceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="agenceEmetteurId")
     */
    private $domAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="agenceDebiteurId")
     */
    private $domAgenceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity=Mutation::class, mappedBy="agenceEmetteur")
     */
    private $mutationAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=Mutation::class, mappedBy="agenceDebiteur")
     */
    private $mutationAgenceDebiteur;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="agencesAutorisees")
     * @ORM\JoinTable(name="agence_user", 
     *      joinColumns={@ORM\JoinColumn(name="agence_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $usersAutorises;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="agenceEmetteurId")
     */
    private $tkiAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="agenceDebiteurId")
     */
    private $tkiAgenceDebiteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeAppro::class, mappedBy="agenceEmetteur")
     */
    private $daAgenceEmetteur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeAppro::class, mappedBy="agenceDebiteur")
     */
    private $daAgenceDebiteur;

    public function __construct()
    {
        $this->agenceServices = new ArrayCollection();
        $this->casiers = new ArrayCollection();
        $this->ditAgenceEmetteur = new ArrayCollection();
        $this->ditAgenceDebiteur = new ArrayCollection();
        $this->badmAgenceEmetteur = new ArrayCollection();
        $this->badmAgenceDebiteur = new ArrayCollection();
        $this->usersAutorises = new ArrayCollection();
        $this->domAgenceEmetteur = new ArrayCollection();
        $this->domAgenceDebiteur = new ArrayCollection();
        $this->tkiAgenceEmetteur = new ArrayCollection();
        $this->tkiAgenceDebiteur = new ArrayCollection();
        $this->daAgenceEmetteur = new ArrayCollection();
        $this->daAgenceDebiteur = new ArrayCollection();
        $this->mutationAgenceEmetteur = new ArrayCollection();
        $this->mutationAgenceDebiteur = new ArrayCollection();
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
        return $this->agenceServices->map(fn(AgenceService $as) => $as->getService());
    }

    /**
     * @return Collection<int, AgenceService>
     */
    public function getAgenceServices(): Collection
    {
        return $this->agenceServices;
    }

    public function addAgenceService(AgenceService $agenceService): self
    {
        if (!$this->agenceServices->contains($agenceService)) {
            $this->agenceServices[] = $agenceService;
            $agenceService->setAgence($this);
        }

        return $this;
    }

    public function removeAgenceService(AgenceService $agenceService): self
    {
        if ($this->agenceServices->removeElement($agenceService)) {
            // set the owning side to null (unless already changed)
            if ($agenceService->getAgence() === $this) {
                $agenceService->setAgence(null);
            }
        }

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


    /** DIT */

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


    /** BADM */
    /**
     * Get the value of demandeInterventions
     */
    public function getbadmAgenceEmetteurs()
    {
        return $this->badmAgenceEmetteur;
    }

    public function addbadmAgenceEmetteur(Badm $badm): self
    {
        if (!$this->badmAgenceEmetteur->contains($badm)) {
            $this->badmAgenceEmetteur[] = $badm;
            $badm->setAgenceEmetteurId($this);
        }

        return $this;
    }

    public function removebadmAgenceEmetteur(Badm $badmAgenceEmetteur): self
    {
        if ($this->badmAgenceEmetteur->contains($badmAgenceEmetteur)) {
            $this->badmAgenceEmetteur->removeElement($badmAgenceEmetteur);
            if ($badmAgenceEmetteur->getAgenceEmetteurId() === $this) {
                $badmAgenceEmetteur->setAgenceEmetteurId(null);
            }
        }

        return $this;
    }
    public function setbadmAgenceEmetteurs($badmAgenceEmetteur)
    {
        $this->badmAgenceEmetteur = $badmAgenceEmetteur;

        return $this;
    }



    /**
     * Get the value of demandeInterventions
     */
    public function getbadmAgenceDebiteurs()
    {
        return $this->badmAgenceDebiteur;
    }

    public function addbadmAgenceDebiteurs(Badm $badm): self
    {
        if (!$this->badmAgenceDebiteur->contains($badm)) {
            $this->badmAgenceDebiteur[] = $badm;
            $badm->setAgenceDebiteurId($this);
        }

        return $this;
    }

    public function removebadmAgenceDebiteur(Badm $badmAgenceDebiteur): self
    {
        if ($this->badmAgenceDebiteur->contains($badmAgenceDebiteur)) {
            $this->badmAgenceDebiteur->removeElement($badmAgenceDebiteur);
            if ($badmAgenceDebiteur->getAgenceDebiteurId() === $this) {
                $badmAgenceDebiteur->setAgenceDebiteurId(null);
            }
        }

        return $this;
    }

    public function setbadmAgenceDebiteurs($badmAgenceDebiteur)
    {
        $this->badmAgenceDebiteur = $badmAgenceDebiteur;

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

    /** TKI */

    public function getTkiAgenceEmetteur()
    {
        return $this->tkiAgenceEmetteur;
    }

    public function addTkiAgenceEmetteur(DemandeSupportInformatique $tkiAgenceEmetteur): self
    {
        if (!$this->tkiAgenceEmetteur->contains($tkiAgenceEmetteur)) {
            $this->tkiAgenceEmetteur[] = $tkiAgenceEmetteur;
            $tkiAgenceEmetteur->setAgenceEmetteurId($this);
        }

        return $this;
    }

    public function removeTkiAgenceEmetteur(DemandeSupportInformatique $tkiAgenceEmetteur): self
    {
        if ($this->tkiAgenceEmetteur->contains($tkiAgenceEmetteur)) {
            $this->tkiAgenceEmetteur->removeElement($tkiAgenceEmetteur);
            if ($tkiAgenceEmetteur->getAgenceEmetteurId() === $this) {
                $tkiAgenceEmetteur->setAgenceEmetteurId(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of demandeInterventions
     */
    public function getTkiAgenceDebiteur()
    {
        return $this->tkiAgenceDebiteur;
    }

    public function addTkiAgenceDebiteur(DemandeSupportInformatique $tkiAgenceDebiteur): self
    {
        if (!$this->tkiAgenceDebiteur->contains($tkiAgenceDebiteur)) {
            $this->tkiAgenceDebiteur[] = $tkiAgenceDebiteur;
            $tkiAgenceDebiteur->setAgenceDebiteurId($this);
        }

        return $this;
    }

    public function removeTkiAgenceDebiteur(DemandeSupportInformatique $tkiAgenceDebiteur): self
    {
        if ($this->tkiAgenceDebiteur->contains($tkiAgenceDebiteur)) {
            $this->tkiAgenceDebiteur->removeElement($tkiAgenceDebiteur);
            if ($tkiAgenceDebiteur->getAgenceDebiteurId() === $this) {
                $tkiAgenceDebiteur->setAgenceDebiteurId(null);
            }
        }

        return $this;
    }

    /** MUTATION */

    /**
     * Get the value of mutationAgenceEmetteur
     */
    public function getMutationAgenceEmetteur()
    {
        return $this->mutationAgenceEmetteur;
    }

    public function addMutationAgenceEmetteur(Mutation $mutation): self
    {
        if (!$this->mutationAgenceEmetteur->contains($mutation)) {
            $this->mutationAgenceEmetteur[] = $mutation;
            $mutation->setAgenceEmetteur($this);
        }

        return $this;
    }

    public function removeMutationAgenceEmetteur(Mutation $mutation): self
    {
        if ($this->mutationAgenceEmetteur->contains($mutation)) {
            $this->mutationAgenceEmetteur->removeElement($mutation);
            if ($mutation->getAgenceEmetteur() === $this) {
                $mutation->setAgenceEmetteur(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of mutationAgenceEmetteur
     *
     * @return  self
     */
    public function setMutationAgenceEmetteur($mutationAgenceEmetteur)
    {
        $this->mutationAgenceEmetteur = $mutationAgenceEmetteur;

        return $this;
    }

    /**
     * Get the value of mutationAgenceDebiteur
     */
    public function getMutationAgenceDebiteur()
    {
        return $this->mutationAgenceDebiteur;
    }

    public function addMutationAgenceDebiteur(Mutation $mutation): self
    {
        if (!$this->mutationAgenceDebiteur->contains($mutation)) {
            $this->mutationAgenceDebiteur[] = $mutation;
            $mutation->setAgenceDebiteur($this);
        }

        return $this;
    }

    public function removeMutationAgenceDebiteur(Mutation $mutation): self
    {
        if ($this->mutationAgenceDebiteur->contains($mutation)) {
            $this->mutationAgenceDebiteur->removeElement($mutation);
            if ($mutation->getAgenceDebiteur() === $this) {
                $mutation->setAgenceDebiteur(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of mutationAgenceDebiteur
     *
     * @return  self
     */
    public function setMutationAgenceDebiteur($mutationAgenceDebiteur)
    {
        $this->mutationAgenceDebiteur = $mutationAgenceDebiteur;

        return $this;
    }

    /**
     * Get the value of daAgenceEmetteur
     */
    public function getDaAgenceEmetteur()
    {
        return $this->daAgenceEmetteur;
    }

    /**
     * Set the value of daAgenceEmetteur
     */
    public function setDaAgenceEmetteur($daAgenceEmetteur): self
    {
        $this->daAgenceEmetteur = $daAgenceEmetteur;
        return $this;
    }

    /**
     * Get the value of daAgenceDebiteur
     */
    public function getDaAgenceDebiteur()
    {
        return $this->daAgenceDebiteur;
    }

    /**
     * Set the value of daAgenceDebiteur
     */
    public function setDaAgenceDebiteur($daAgenceDebiteur): self
    {
        $this->daAgenceDebiteur = $daAgenceDebiteur;
        return $this;
    }
}
