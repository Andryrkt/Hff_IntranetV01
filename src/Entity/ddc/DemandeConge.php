<?php

namespace App\Entity\ddc;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\AgenceServiceIrium;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ddc\DemandeCongeRepository")
 * @ORM\Table(name="demande_de_conge")
 */
class DemandeConge
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="Type_Demande", type="string", length=100)
     */
    private ?string $typeDemande = null;

    /**
     * @ORM\Column(name="Numero_Demande", type="string", length=11)
     */
    private ?string $numeroDemande = null;

    /**
     * @ORM\Column(name="Matricule", type="string", length=4)
     */
    private ?string $matricule = null;

    /**
     * @ORM\Column(name="Nom_Prenoms", type="string", length=100)
     */
    private ?string $nomPrenoms = null;

    /**
     * @ORM\Column(name="Date_Demande", type="date")
     */
    private ?\DateTimeInterface $dateDemande = null;

    /**
     * @ORM\Column(name="Agence_Debiteur", type="string", length=10)
     */
    private ?string $agenceDebiteur = null;



    /**
     * @ORM\Column(name="Adresse_Mail_Demandeur", type="string", length=100)
     */
    private ?string $adresseMailDemandeur = null;

    /**
     * @ORM\Column(name="Sous_type_document", type="string", length=100)
     */
    private ?string $sousTypeDocument = null;

    /**
     * @ORM\Column(name="Duree_Conge", type="decimal", precision=5, scale=2)
     */
    private ?string $dureeConge = null;

    /**
     * @ORM\Column(name="Date_Debut", type="date")
     */
    private ?\DateTimeInterface $dateDebut = null;

    /**
     * @ORM\Column(name="Date_Fin", type="date")
     */
    private ?\DateTimeInterface $dateFin = null;

    /**
     * @ORM\Column(name="Solde_Conge", type="decimal", precision=5, scale=2)
     */
    private ?string $soldeConge = null;

    /**
     * @ORM\Column(name="Motif_Conge", type="string", length=100)
     */
    private ?string $motifConge = null;

    /**
     * @ORM\Column(name="Statut_Demande", type="string", length=50)
     */
    private ?string $statutDemande = null;

    /**
     * @ORM\Column(name="Date_Statut", type="date")
     */
    private ?\DateTimeInterface $dateStatut = null;

    /**
     * @ORM\Column(name="pdf_demande", type="text", nullable=true)
     */
    private ?string $pdfDemande = null;

    private ?string $codeAgenceService = '-';

    /**
     * @ORM\ManyToOne(targetEntity=AgenceServiceIrium::class, inversedBy="demandeDeConge")
     * @ORM\JoinColumn(name="Agence_Service", referencedColumnName="service_sage_paie")
     */
    private $agenceServiceirium;


    private $groupeDirection;

    /** ==================================================================================
     * Getters et Setters (inchangés)
     *=============================================================================*/

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTypeDemande(): ?string
    {
        return $this->typeDemande;
    }
    public function setTypeDemande(?string $typeDemande): self
    {
        $this->typeDemande = $typeDemande;
        return $this;
    }
    public function getNumeroDemande(): ?string
    {
        return $this->numeroDemande;
    }
    public function setNumeroDemande(?string $numeroDemande): self
    {
        $this->numeroDemande = $numeroDemande;
        return $this;
    }
    public function getMatricule(): ?string
    {
        return $this->matricule;
    }
    public function setMatricule(?string $matricule): self
    {
        $this->matricule = $matricule;
        return $this;
    }
    public function getNomPrenoms(): ?string
    {
        return $this->nomPrenoms;
    }
    public function setNomPrenoms(?string $nomPrenoms): self
    {
        $this->nomPrenoms = $nomPrenoms;
        return $this;
    }
    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }
    public function setDateDemande(?\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }
    public function getAgenceDebiteur(): ?string
    {
        return $this->agenceDebiteur;
    }
    public function setAgenceDebiteur(?string $agenceDebiteur): self
    {
        $this->agenceDebiteur = $agenceDebiteur;
        return $this;
    }

    public function getAdresseMailDemandeur(): ?string
    {
        return $this->adresseMailDemandeur;
    }
    public function setAdresseMailDemandeur(?string $adresseMailDemandeur): self
    {
        $this->adresseMailDemandeur = $adresseMailDemandeur;
        return $this;
    }
    public function getSousTypeDocument(): ?string
    {
        return $this->sousTypeDocument;
    }
    public function setSousTypeDocument(?string $sousTypeDocument): self
    {
        $this->sousTypeDocument = $sousTypeDocument;
        return $this;
    }
    public function getDureeConge(): ?string
    {
        return $this->dureeConge;
    }
    public function setDureeConge(?string $dureeConge): self
    {
        $this->dureeConge = $dureeConge;
        return $this;
    }
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }
    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }
    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }
    public function getSoldeConge(): ?string
    {
        return $this->soldeConge;
    }
    public function setSoldeConge(?string $soldeConge): self
    {
        $this->soldeConge = $soldeConge;
        return $this;
    }
    public function getMotifConge(): ?string
    {
        return $this->motifConge;
    }
    public function setMotifConge(?string $motifConge): self
    {
        $this->motifConge = $motifConge;
        return $this;
    }
    public function getStatutDemande(): ?string
    {
        return $this->statutDemande;
    }
    public function setStatutDemande(?string $statutDemande): self
    {
        $this->statutDemande = $statutDemande;
        return $this;
    }
    public function getDateStatut(): ?\DateTimeInterface
    {
        return $this->dateStatut;
    }
    public function setDateStatut(?\DateTimeInterface $dateStatut): self
    {
        $this->dateStatut = $dateStatut;
        return $this;
    }
    public function getPdfDemande(): ?string
    {
        return $this->pdfDemande;
    }
    public function setPdfDemande(?string $pdfDemande): self
    {
        $this->pdfDemande = $pdfDemande;
        return $this;
    }

    /**
     * Get the value of codeAgenceService
     */
    public function getCodeAgenceService(): ?string
    {
        return $this->codeAgenceService;
    }

    /**
     * Set the value of codeAgenceService
     */
    public function setCodeAgenceService(?string $codeAgenceService): self
    {
        $this->codeAgenceService = $codeAgenceService;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'typeDemande' => $this->typeDemande,
            'numeroDemande' => $this->numeroDemande,
            'matricule' => $this->matricule,
            'nomPrenoms' => $this->nomPrenoms,
            'dateDemande' => $this->dateDemande,
            'agenceDebiteur' => $this->agenceDebiteur,
            'agence' => $this->agenceDebiteur,
            'agenceService' => $this->agenceServiceirium ? $this->agenceServiceirium->getServicesagepaie() : null,
            'adresseMailDemandeur' => $this->adresseMailDemandeur,
            'sousTypeDocument' => $this->sousTypeDocument,
            'dureeConge' => $this->dureeConge,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'soldeConge' => $this->soldeConge,
            'motifConge' => $this->motifConge,
            'statutDemande' => $this->statutDemande,
            'dateStatut' => $this->dateStatut,
            'pdfDemande' => $this->pdfDemande,
            'groupeDirection' => $this->groupeDirection,
        ];
    }

    /**
     * Get the value of agenceServiceirium
     */
    public function getAgenceServiceirium()
    {
        return $this->agenceServiceirium;
    }

    /**
     * Set the value of agenceServiceirium
     */
    public function setAgenceServiceirium($agenceServiceirium): self
    {
        $this->agenceServiceirium = $agenceServiceirium;

        return $this;
    }

    /**
     * Get the value of groupeDirection
     */
    public function getGroupeDirection()
    {
        return $this->groupeDirection;
    }

    /**
     * Set the value of groupeDirection
     */
    public function setGroupeDirection($groupeDirection): self
    {
        $this->groupeDirection = $groupeDirection;

        return $this;
    }
}
