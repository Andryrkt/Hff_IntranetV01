<?php

namespace App\Entity\da;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeApproParentLine;
use Doctrine\Common\Collections\Collection;
use App\Repository\da\DemandeApproParentRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=DemandeApproParentRepository::class)
 * @ORM\Table(name="Demande_Appro_P")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeApproParent
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_appro")
     */
    private ?string $numeroDemandeAppro = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = '';

    /**
     * @ORM\Column(type="string", length=100, name="objet_dal")
     */
    private string $objetDal;

    /**
     * @ORM\Column(type="string", length=1000, name="detail_dal", nullable=true)
     */
    private ?string $detailDal = null;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="daAgenceEmetteur")
     * @ORM\JoinColumn(name="agence_emmetteur_id", referencedColumnName="id")
     */
    private  $agenceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="daServiceEmetteur")
     * @ORM\JoinColumn(name="service_emmetteur_id", referencedColumnName="id")
     */
    private  $serviceEmetteur;

    /**
     * @ORM\Column(type="string", length=6, name="agence_service_emmeteur")
     */
    private string $agenceServiceEmetteur;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="daAgenceDebiteur")
     * @ORM\JoinColumn(name="agence_debiteur_id", referencedColumnName="id")
     */
    private  $agenceDebiteur;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="daServiceDebiteur")
     * @ORM\JoinColumn(name="service_debiteur_id", referencedColumnName="id")
     */
    private  $serviceDebiteur;

    /**
     * @ORM\Column(type="string", length=6, name="agence_service_debiteur")
     */
    private string $agenceServiceDebiteur;

    /**
     * @ORM\Column(type="datetime", name="date_heure_fin_souhaitee", nullable=true)
     */
    private $dateFinSouhaite;

    /**
     * @ORM\Column(type="string", length=100, name="statut_dal", nullable=true)
     */
    private string $statutDal;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demandeApproParentUser")
     * @ORM\JoinColumn(nullable=true, name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=50, name="niveau_urgence")
     */
    private string $niveauUrgence = '';

    /**
     * @ORM\Column(type="string", length=50, name="code_centrale")
     */
    private ?string $codeCentrale = null;

    /**
     * @ORM\Column(type="string", length=50, name="designation_central")
     */
    private ?string $desiCentrale = null;

    /**
     * @ORM\OneToMany(targetEntity=DemandeApproParentLine::class, mappedBy="demandeApproParent")
     */
    private Collection $demandeApproParentLines;

    private $observation;

    private $debiteur;

    public function __construct()
    {
        $this->demandeApproParentLines = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of numeroDemandeAppro
     */
    public function getNumeroDemandeAppro(): ?string
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     */
    public function setNumeroDemandeAppro(?string $numeroDemandeAppro): self
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

        return $this;
    }

    /**
     * Get the value of demandeur
     */
    public function getDemandeur(): ?string
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     */
    public function setDemandeur(?string $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    /**
     * Get the value of objetDal
     */
    public function getObjetDal(): string
    {
        return $this->objetDal;
    }

    /**
     * Set the value of objetDal
     */
    public function setObjetDal(string $objetDal): self
    {
        $this->objetDal = $objetDal;

        return $this;
    }

    /**
     * Get the value of detailDal
     */
    public function getDetailDal(): ?string
    {
        return $this->detailDal;
    }

    /**
     * Set the value of detailDal
     */
    public function setDetailDal(?string $detailDal): self
    {
        $this->detailDal = $detailDal;

        return $this;
    }

    /**
     * Get the value of agenceEmetteur
     */
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     */
    public function setAgenceEmetteur($agenceEmetteur): self
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    /**
     * Get the value of serviceEmetteur
     */
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set the value of serviceEmetteur
     */
    public function setServiceEmetteur($serviceEmetteur): self
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceServiceEmetteur
     */
    public function getAgenceServiceEmetteur(): string
    {
        return $this->agenceServiceEmetteur;
    }

    /**
     * Set the value of agenceServiceEmetteur
     */
    public function setAgenceServiceEmetteur(string $agenceServiceEmetteur): self
    {
        $this->agenceServiceEmetteur = $agenceServiceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceDebiteur
     */
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set the value of agenceDebiteur
     */
    public function setAgenceDebiteur($agenceDebiteur): self
    {
        $this->agenceDebiteur = $agenceDebiteur;

        return $this;
    }

    /**
     * Get the value of serviceDebiteur
     */
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set the value of serviceDebiteur
     */
    public function setServiceDebiteur($serviceDebiteur): self
    {
        $this->serviceDebiteur = $serviceDebiteur;

        return $this;
    }

    /**
     * Get the value of agenceServiceDebiteur
     */
    public function getAgenceServiceDebiteur(): string
    {
        return $this->agenceServiceDebiteur;
    }

    /**
     * Set the value of agenceServiceDebiteur
     */
    public function setAgenceServiceDebiteur(string $agenceServiceDebiteur): self
    {
        $this->agenceServiceDebiteur = $agenceServiceDebiteur;

        return $this;
    }

    /**
     * Get the value of dateFinSouhaite
     */
    public function getDateFinSouhaite()
    {
        return $this->dateFinSouhaite;
    }

    /**
     * Set the value of dateFinSouhaite
     */
    public function setDateFinSouhaite($dateFinSouhaite): self
    {
        $this->dateFinSouhaite = $dateFinSouhaite;

        return $this;
    }

    /**
     * Get the value of statutDal
     */
    public function getStatutDal(): string
    {
        return $this->statutDal;
    }

    /**
     * Set the value of statutDal
     */
    public function setStatutDal(string $statutDal): self
    {
        $this->statutDal = $statutDal;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the value of user
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of niveauUrgence
     */
    public function getNiveauUrgence(): string
    {
        return $this->niveauUrgence;
    }

    /**
     * Set the value of niveauUrgence
     */
    public function setNiveauUrgence(string $niveauUrgence): self
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }

    /**
     * Get the value of codeCentrale
     */
    public function getCodeCentrale(): ?string
    {
        return $this->codeCentrale;
    }

    /**
     * Set the value of codeCentrale
     */
    public function setCodeCentrale(?string $codeCentrale): self
    {
        $this->codeCentrale = $codeCentrale;

        return $this;
    }

    /**
     * Get the value of desiCentrale
     */
    public function getDesiCentrale(): ?string
    {
        return $this->desiCentrale;
    }

    /**
     * Set the value of desiCentrale
     */
    public function setDesiCentrale(?string $desiCentrale): self
    {
        $this->desiCentrale = $desiCentrale;

        return $this;
    }

    /**
     * Get the value of demandeApproParentLines
     */
    public function getDemandeApproParentLines(): Collection
    {
        return $this->demandeApproParentLines;
    }

    /**
     * Add a demandeApproParentLine
     */
    public function addDemandeApproParentLine(DemandeApproParentLine $demandeApproParentLine): self
    {
        if (!$this->demandeApproParentLines->contains($demandeApproParentLine)) {
            $this->demandeApproParentLines[] = $demandeApproParentLine;
            $demandeApproParentLine->setDemandeApproParent($this);
        }

        return $this;
    }

    /**
     * Remove a demandeApproParentLine
     */
    public function removeDemandeApproParentLine(DemandeApproParentLine $demandeApproParentLine): self
    {
        if ($this->demandeApproParentLines->contains($demandeApproParentLine)) {
            $this->demandeApproParentLines->removeElement($demandeApproParentLine);
            if ($demandeApproParentLine->getDemandeApproParent() === $this) {
                $demandeApproParentLine->setDemandeApproParent(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of debiteur
     */
    public function getDebiteur()
    {
        return $this->debiteur;
    }

    /**
     * Set the value of debiteur
     */
    public function setDebiteur($debiteur): self
    {
        $this->debiteur = $debiteur;

        return $this;
    }

    /**
     * Get the value of observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     */
    public function setObservation($observation): self
    {
        $this->observation = $observation;

        return $this;
    }
}
