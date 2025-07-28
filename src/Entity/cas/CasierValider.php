<?php

namespace App\Entity\cas;

use App\Entity\admin\Agence;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\badm\Badm;
use App\Repository\cas\CasierRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CasierRepository::class)
 * @ORM\Table(name="Casier_Materiels")
 * @ORM\HasLifecycleCallbacks
 */
class CasierValider
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=20, name="Casier")
     *
     * @var string
     */
    private string $casier;

    /**
     * @ORM\Column(type="date", name="Date_Creation")
     */
    private DateTime $dateCreation;

    /**
     * @ORM\Column(type="string", length=15, name="Numero_CAS")
     *
     * @var string
     */
    private string $numeroCas;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="Nom_Session_Utilisateur", referencedColumnName="id")
     */
    private $nomSessionUtilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="Agence_Rattacher", referencedColumnName="id")
     */
    private $agenceRattacher;

    /**
     * @ORM\ManyToOne(targetEntity=StatutDemande::class, inversedBy="casiers")
     * @ORM\JoinColumn(name="id_statut_demande", referencedColumnName="ID_Statut_Demande")
     */
    private $idStatutDemande = null;

    /**
    * @ORM\OneToMany(targetEntity=Badm::class, mappedBy="casierDestinataire")
    */
    private $badms;

    public function __construct()
    {
        $this->badms = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCasier()
    {
        return $this->casier;
    }

    public function setCasier(string $casier): self
    {
        $this->casier = $casier;

        return $this;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getNumeroCas()
    {
        return $this->numeroCas;
    }

    public function setNumeroCas(string $numeroCas): self
    {
        $this->numeroCas = $numeroCas;

        return $this;
    }

    public function getNomSessionUtilisateur()
    {
        return $this->nomSessionUtilisateur;
    }

    public function setNomSessionUtilisateur($nomSessionUtilisateur): self
    {
        $this->nomSessionUtilisateur = $nomSessionUtilisateur;

        return $this;
    }

    public function getAgenceRattacher(): ?Agence
    {
        return $this->agenceRattacher;
    }

    public function setAgenceRattacher(?Agence $agence): self
    {
        $this->agenceRattacher = $agence;

        return $this;
    }

    public function getIdStatutDemande()
    {
        return $this->idStatutDemande;
    }

    public function setIdStatutDemande($idStatutDemande): self
    {
        $this->idStatutDemande = $idStatutDemande;

        return $this;
    }

    public function getBadms(): Collection
    {
        return $this->badms;
    }

    public function addBadm(Badm $badm): self
    {
        if (! $this->badms->contains($badm)) {
            $this->badms[] = $badm;
            $badm->setCasierDestinataire($this);
        }

        return $this;
    }

    public function removeBadm(Badm $badm): self
    {
        if ($this->badms->contains($badm)) {
            $this->badms->removeElement($badm);
            if ($badm->getCasierDestinataire() === $this) {
                $badm->setCasierDestinataire(null);
            }
        }

        return $this;
    }

    public function setBadms($badms): self
    {
        $this->badms = $badms;

        return $this;
    }
}
