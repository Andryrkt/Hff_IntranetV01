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
     * @ORM\OneToMany(targetEntity=Dom::class, mappedBy="idStatutDemande")
     */
    private $doms;


    public function __construct()
    {
        $this->doms = new ArrayCollection();
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


    public function __toString()
    {
        return $this->description;
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
}
