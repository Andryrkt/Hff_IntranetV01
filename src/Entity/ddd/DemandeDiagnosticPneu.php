<?php

namespace App\Entity\ddd;

use App\Entity\ddd\Chantier;
use App\Entity\ddd\DiagnosticPneu;
use App\Repository\ddd\DemandeDiagnosticPneuRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DemandeDiagnosticPneuRepository::class)
 * @ORM\Table(name="demande_diagnostic_pneu")
 * @ORM\HasLifecycleCallbacks
 */
class DemandeDiagnosticPneu
{

    public const STATUT_A_TRAITER_ATELIER = 'a traiter atelier';
    public const STATUT_DIAG_EN_COURS   = 'diag en cours';
    public const STATUT_TRAITEE_ATELIER   = 'traitee atelier';
    public const STATUT_CLOTUREE          = 'cloturee';

    public const STATUTS = [
        'À traiter atelier' => self::STATUT_A_TRAITER_ATELIER,
        'Diag en cours'   => self::STATUT_DIAG_EN_COURS,
        'Traitée atelier'   => self::STATUT_TRAITEE_ATELIER,
        'Clôturée'          => self::STATUT_CLOTUREE,
    ];


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=12, unique=true, name="numero_demande")
     */
    private ?string $numeroDemande = null;

    /**
     * @ORM\ManyToOne(targetEntity=Chantier::class)
     * @ORM\JoinColumn(name="id_chantier", referencedColumnName="id_chantier", nullable=false)
     */
    private ?Chantier $chantier = null;

    /**
     * @ORM\OneToMany(targetEntity=DiagnosticPneu::class, mappedBy="demande", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $diagnosticPneus;

    /**
     * @ORM\Column(type="integer", name="id_materiel")
     */
    private ?int $idMateriel = null;

    /**
     * @ORM\Column(type="string", length=20, name="numero_parc_materiel" , nullable=true)
     */
    private ?string $numeroParcMateriel = null;

    /**
     * @ORM\Column(type="string", length=50, name="marque_materiel" ,nullable=true )
     */
    private ?string $marqueMateriel = null;

    /**
     * @ORM\Column(type="string", length=50, name="type_materiel")
     */
    private ?string $typeMateriel = null;

    /**
     * @ORM\Column(type="string", length=150, name="designation_materiel")
     */
    private ?string $designationMateriel = null;

    /**
     * @ORM\Column(type="date", name="date_depart_chantier")
     */
    private ?DateTime $dateDepartChantier = null;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $livraison = null;

    /**
     * @ORM\Column(type="smallint", name="nb_pneu_sur_machine")
     */
    private ?int $nbPneuSurMachine = null;

    /**
     * @ORM\Column(type="smallint", name="nb_pneu_secours")
     */
    private ?int $nbPneuSecours = null;

    /**
     * @ORM\Column(type="smallint", name="nb_pneu_a_diagnostiquer")
     */
    private ?int $nbPneuADiagnostiquer = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $observation = null;

    /**
     * @ORM\Column(type="text", nullable=true , name ="observation_global_atelier")
     */
    private ?string $observationGlobalAtelier = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $demandeur = null;

    /**
     * @ORM\Column(type="datetime", name="date_creation")
     */
    private ?DateTime $dateCreation = null;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $statut = 'a traiter atelier';

    /**
     * @ORM\Column(type="string", length=12, nullable=true, name="numero_dit")
     */
    private ?string $numeroDit = null;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, name="numero_or")
     */
    private ?string $numeroOr = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private  $piecesJointes;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    private ?array $motifs = [];

    public function __construct()
    {
        $this->dateCreation = new DateTime();
        $this->statut = self::STATUT_A_TRAITER_ATELIER;
        $this->diagnosticPneus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroDemande(): ?string
    {
        return $this->numeroDemande;
    }

    public function setNumeroDemande(string $numeroDemande): self
    {
        $this->numeroDemande = $numeroDemande;
        return $this;
    }

    public function getChantier(): ?Chantier
    {
        return $this->chantier;
    }

    public function setChantier(?Chantier $chantier): self
    {
        $this->chantier = $chantier;
        return $this;
    }

    public function getIdMateriel(): ?int
    {
        return $this->idMateriel;
    }

    public function setIdMateriel(int $idMateriel): self
    {
        $this->idMateriel = $idMateriel;
        return $this;
    }

    public function getNumeroParcMateriel(): ?string
    {
        return $this->numeroParcMateriel;
    }

    public function setNumeroParcMateriel(string $numeroParcMateriel): self
    {
        $this->numeroParcMateriel = $numeroParcMateriel;
        return $this;
    }

    public function getMarqueMateriel(): ?string
    {
        return $this->marqueMateriel;
    }

    public function setMarqueMateriel(string $marqueMateriel): self
    {
        $this->marqueMateriel = $marqueMateriel;
        return $this;
    }

    public function getTypeMateriel(): ?string
    {
        return $this->typeMateriel;
    }

    public function setTypeMateriel(string $typeMateriel): self
    {
        $this->typeMateriel = $typeMateriel;
        return $this;
    }

    public function getDesignationMateriel(): ?string
    {
        return $this->designationMateriel;
    }

    public function setDesignationMateriel(string $designationMateriel): self
    {
        $this->designationMateriel = $designationMateriel;
        return $this;
    }

    public function getDateDepartChantier(): ?DateTime
    {
        return $this->dateDepartChantier;
    }

    public function setDateDepartChantier(DateTime $dateDepartChantier): self
    {
        $this->dateDepartChantier = $dateDepartChantier;
        return $this;
    }

    public function getLivraison(): ?string
    {
        return $this->livraison;
    }

    public function setLivraison(?string $livraison): self
    {
        $this->livraison = $livraison;
        return $this;
    }

    public function getNbPneuSurMachine(): ?int
    {
        return $this->nbPneuSurMachine;
    }

    public function setNbPneuSurMachine(int $nbPneuSurMachine): self
    {
        $this->nbPneuSurMachine = $nbPneuSurMachine;
        return $this;
    }

    public function getNbPneuSecours(): ?int
    {
        return $this->nbPneuSecours;
    }

    public function setNbPneuSecours(int $nbPneuSecours): self
    {
        $this->nbPneuSecours = $nbPneuSecours;
        return $this;
    }

    public function getNbPneuADiagnostiquer(): ?int
    {
        return $this->nbPneuADiagnostiquer;
    }

    public function setNbPneuADiagnostiquer(int $nbPneuADiagnostiquer): self
    {
        $this->nbPneuADiagnostiquer = $nbPneuADiagnostiquer;
        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;
        return $this;
    }
    public function getObservationGlobalAtelier(): ?string
    {
        return $this->observationGlobalAtelier;
    }

    public function setObservationGlobalAtelier(?string $observationGlobalAtelier): self
    {
        $this->observationGlobalAtelier = $observationGlobalAtelier;
        return $this;
    }

    public function getDemandeur(): ?string
    {
        return $this->demandeur;
    }

    public function setDemandeur(string $demandeur): self
    {
        $this->demandeur = $demandeur;
        return $this;
    }

    public function getDateCreation(): ?DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNumeroDit(): ?string
    {
        return $this->numeroDit;
    }

    public function setNumeroDit(?string $numeroDit): self
    {
        $this->numeroDit = $numeroDit;
        return $this;
    }

    public function getNumeroOr(): ?string
    {
        return $this->numeroOr;
    }

    public function setNumeroOr(?string $numeroOr): self
    {
        $this->numeroOr = $numeroOr;
        return $this;
    }


    public function getPiecesJointes()
    {
        return $this->piecesJointes;
    }


    public function setPiecesJointes(?array $piecesJointes): self
    {
        $this->piecesJointes = $piecesJointes;
        return $this;
    }
    public function getMotifs()
    {
        return $this->motifs;
    }


    public function setMotifs(?array $motifs): self
    {
        $this->motifs = $motifs;

        return $this;
    }

    public function getDiagnosticPneus(): Collection
    {
        return $this->diagnosticPneus;
    }

    public function addDiagnosticPneu(DiagnosticPneu $diagnosticPneu): self
    {
        if (!$this->diagnosticPneus->contains($diagnosticPneu)) {
            $this->diagnosticPneus[] = $diagnosticPneu;
            $diagnosticPneu->setDemande($this);
        }
        return $this;
    }

    public function removeDiagnosticPneu(DiagnosticPneu $diagnosticPneu): self
    {
        if ($this->diagnosticPneus->contains($diagnosticPneu)) {
            $this->diagnosticPneus->removeElement($diagnosticPneu);
            // set the owning side to null (unless already changed)
            if ($diagnosticPneu->getDemande() === $this) {
                $diagnosticPneu->setDemande(null);
            }
        }
        return $this;
    }
}
