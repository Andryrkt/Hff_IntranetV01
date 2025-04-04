<?php

namespace App\Entity\admin;

use App\Entity\dom\Dom;
use App\Entity\badm\Badm;
use App\Entity\cas\Casier;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\mutation\Mutation;
use App\Form\demandeInterventionType;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\Collection;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\StatutDemandeRepository;
use App\Entity\admin\tik\TkiStatutTicketInformatique;

/**
 * @ORM\Entity(repositoryClass=StatutDemandeRepository::class)
 * @ORM\Table(name="Statut_demande")
 * @ORM\HasLifecycleCallbacks
 */
class StatutDemande
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID_Statut_Demande")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Application")
     */
    private $codeApp;

    /**
     * @ORM\Column(type="string", length=3, name="Code_Statut")
     */
    private $codeStatut;

    /**
     * @ORM\Column(type="string", length=50, name="Description")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="statutDemande")
     */
    private $badms;

    /**
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="idStatutDemande")
     */
    private $demandeInterventions;

    /**
     * @ORM\OneToMany(targetEntity=Casier::class, mappedBy="idStatutDemande")
     */
    private $casiers;

    /**
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="idStatutDemande")
     */
    private $doms;

    /**
     * @ORM\OneToMany(targetEntity=Mutation::class, mappedBy="statutDemande")
     */
    private $mutation;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="idStatutDemande")
     */
    private $supportInfo;

    /**
     * @ORM\OneToMany(targetEntity=TkiStatutTicketInformatique::class, mappedBy="idStatutDemande")
     */
    private $statutTik;

    public function __construct()
    {
        $this->badms = new ArrayCollection();
        $this->demandeInterventions = new ArrayCollection();
        $this->casiers = new ArrayCollection();
        $this->doms = new ArrayCollection();
        $this->supportInfo = new ArrayCollection();
        $this->statutTik = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCodeApp(): string
    {
        return $this->codeApp;
    }

    public function setCodeApp(string $codeApp): self
    {
        $this->codeApp = $codeApp;
        return $this;
    }

    public function getCodeStatut(): string
    {
        return $this->codeStatut;
    }

    public function setCodeStatut(string $codeStatut): self
    {
        $this->codeStatut = $codeStatut;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getBadms(): Collection
    {
        return $this->badms;
    }

    public function addBadm(Badm $badm): self
    {
        if (!$this->badms->contains($badm)) {
            $this->badms[] = $badm;
            $badm->setStatutDemande($this);
        }
        return $this;
    }

    public function removeBadm(Badm $badm): self
    {
        if ($this->badms->contains($badm)) {
            $this->badms->removeElement($badm);
            if ($badm->getStatutDemande() === $this) {
                $badm->setStatutDemande(null);
            }
        }
        return $this;
    }

    public function setBadms($badms): self
    {
        $this->badms = $badms;
        return $this;
    }

    public function __toString()
    {
        return $this->description;
    }

    /**
     * Get the value of demandeInterventions
     */
    public function getDemandeInterventions()
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setIdStatutDemande($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getIdStatutDemande() === $this) {
                $demandeIntervention->setIdStatutDemande(null);
            }
        }

        return $this;
    }
    public function setDemandeInterventions($demandeInterventions)
    {
        $this->demandeInterventions = $demandeInterventions;

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
            $casier->setIdStatutDemande($this);
        }

        return $this;
    }

    public function removeCasier(Casier $casier): self
    {
        if ($this->casiers->contains($casier)) {
            $this->casiers->removeElement($casier);
            if ($casier->getIdStatutDemande() === $this) {
                $casier->setIdStatutDemande(null);
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
    public function getDoms()
    {
        return $this->doms;
    }

    public function addDom(Dom $doms): self
    {
        if (!$this->doms->contains($doms)) {
            $this->doms[] = $doms;
            $doms->setIdStatutDemande($this);
        }

        return $this;
    }

    public function removeDom(Dom $doms): self
    {
        if ($this->doms->contains($doms)) {
            $this->doms->removeElement($doms);
            if ($doms->getIdStatutDemande() === $this) {
                $doms->setIdStatutDemande($this);
            }
        }

        return $this;
    }
    public function setDoms($doms)
    {
        $this->doms = $doms;

        return $this;
    }

    /**
     * Get the value of demandeInterventions
     */
    public function getSupportInfo()
    {
        return $this->supportInfo;
    }

    public function addSupportInfo(DemandeSupportInformatique $supportInfo): self
    {
        if (!$this->supportInfo->contains($supportInfo)) {
            $this->supportInfo[] = $supportInfo;
            $supportInfo->setIdStatutDemande($this);
        }

        return $this;
    }

    public function removeSupportInfo(DemandeSupportInformatique $supportInfo): self
    {
        if ($this->supportInfo->contains($supportInfo)) {
            $this->supportInfo->removeElement($supportInfo);
            if ($supportInfo->getIdStatutDemande() === $this) {
                $supportInfo->setIdStatutDemande($this);
            }
        }

        return $this;
    }


    /**
     * Get the value of demandeInterventions
     */
    public function getStatutTik()
    {
        return $this->statutTik;
    }

    public function addStatutTik(TkiStatutTicketInformatique $statutTik): self
    {
        if (!$this->statutTik->contains($statutTik)) {
            $this->statutTik[] = $statutTik;
            $statutTik->setIdStatutDemande($this);
        }

        return $this;
    }

    public function removeStatutTik(TkiStatutTicketInformatique $statutTik): self
    {
        if ($this->statutTik->contains($statutTik)) {
            $this->statutTik->removeElement($statutTik);
            if ($statutTik->getIdStatutDemande() === $this) {
                $statutTik->setIdStatutDemande($this);
            }
        }

        return $this;
    }

    /**
     * Get the value of mutation
     */
    public function getMutation()
    {
        return $this->mutation;
    }

    public function addMutation(Mutation $mutation): self
    {
        if (!$this->mutation->contains($mutation)) {
            $this->mutation[] = $mutation;
            $mutation->setStatutDemande($this);
        }

        return $this;
    }

    public function removeMutation(Mutation $mutation): self
    {
        if ($this->mutation->contains($mutation)) {
            $this->mutation->removeElement($mutation);
            if ($mutation->getStatutDemande() === $this) {
                $mutation->setStatutDemande($this);
            }
        }

        return $this;
    }

    /**
     * Set the value of mutation
     *
     * @return  self
     */
    public function setMutation($mutation)
    {
        $this->mutation = $mutation;

        return $this;
    }
}
