<?php

namespace App\Entity\bdc;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\bdc\BonDeCaisseRepository;

/**
 * @ORM\Entity(repositoryClass="App\Repository\bdc\BonDeCaisseRepository")
 * @ORM\Table(name="Demande_de_bon_de_caisse")
 */
class BonDeCaisse
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
     * @ORM\Column(name="Date_Demande", type="date")
     */
    private ?\DateTimeInterface $dateDemande = null;

    /**
     * @ORM\Column(name="Caisse_Retrait", type="string", length=30)
     */
    private ?string $caisseRetrait = null;

    /**
     * @ORM\Column(name="Type_Paiement", type="string", length=30)
     */
    private ?string $typePaiement = null;

    /**
     * @ORM\Column(name="Agence_Debiteur", type="string", length=10)
     */
    private ?string $agenceDebiteur = null;

    /**
     * @ORM\Column(name="Service_Debiteur", type="string", length=10)
     */
    private ?string $serviceDebiteur = null;
    /**
     * @ORM\Column(name="agence_emetteur", type="string", length=10)
     */
    private ?string $agenceEmetteur = null;

    /**
     * @ORM\Column(name="service_emetteur", type="string", length=10)
     */
    private ?string $serviceEmetteur = null;

    /**
     * @ORM\Column(name="Retrait_Lie", type="string", length=50)
     */
    private ?string $retraitLie = null;

    /**
     * @ORM\Column(name="Matricule", type="string", length=4)
     */
    private ?string $matricule = null;

    /**
     * @ORM\Column(name="Adresse_Mail_Demandeur", type="string", length=100)
     */
    private ?string $adresseMailDemandeur = null;

    /**
     * @ORM\Column(name="Motif_Demande", type="string", length=300)
     */
    private ?string $motifDemande = null;

    /**
     * @ORM\Column(name="Montant_Payer", type="decimal", precision=15, scale=2)
     */
    private ?string $montantPayer = null;

    /**
     * @ORM\Column(name="Devise", type="string", length=3)
     */
    private ?string $devise = null;

    /**
     * @ORM\Column(name="Statut_Demande", type="string", length=50)
     */
    private ?string $statutDemande = null;

    /**
     * @ORM\Column(name="Date_Statut", type="date")
     */
    private ?\DateTimeInterface $dateStatut = null;

    /**
     * @ORM\Column(name="pdf_demande", type="string", length=255, nullable=true)
     */
    private ?string $pdfDemande = null;

    /**
     * @ORM\Column(name="nom_validateur_final", type="string", length=255, nullable=true)
     */
    private ?string $nomValidateurFinal;

    private ?\DateTimeInterface $dateDemandeFin = null;

    /** ===================================================================================
     * Getter and setter
     *==============================================================================*/

    public function getDateDemandeFin(): ?\DateTimeInterface
    {
        return $this->dateDemandeFin;
    }

    public function setDateDemandeFin(?\DateTimeInterface $dateDemandeFin): self
    {
        $this->dateDemandeFin = $dateDemandeFin;
        return $this;
    }

    // Getters et Setters
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
    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }
    public function setDateDemande(?\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }
    public function getCaisseRetrait(): ?string
    {
        return $this->caisseRetrait;
    }
    public function setCaisseRetrait(?string $caisseRetrait): self
    {
        $this->caisseRetrait = $caisseRetrait;
        return $this;
    }
    public function getTypePaiement(): ?string
    {
        return $this->typePaiement;
    }
    public function setTypePaiement(?string $typePaiement): self
    {
        $this->typePaiement = $typePaiement;
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
    public function getServiceDebiteur(): ?string
    {
        return $this->serviceDebiteur;
    }
    public function setServiceDebiteur(?string $serviceDebiteur): self
    {
        $this->serviceDebiteur = $serviceDebiteur;
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
     *
     * @return  self
     */
    public function setAgenceEmetteur($agenceEmetteur)
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
     *
     * @return  self
     */
    public function setServiceEmetteur($serviceEmetteur)
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    public function getRetraitLie(): ?string
    {
        return $this->retraitLie;
    }
    public function setRetraitLie(?string $retraitLie): self
    {
        $this->retraitLie = $retraitLie;
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
    public function getAdresseMailDemandeur(): ?string
    {
        return $this->adresseMailDemandeur;
    }
    public function setAdresseMailDemandeur(?string $adresseMailDemandeur): self
    {
        $this->adresseMailDemandeur = $adresseMailDemandeur;
        return $this;
    }
    public function getMotifDemande(): ?string
    {
        return $this->motifDemande;
    }
    public function setMotifDemande(?string $motifDemande): self
    {
        $this->motifDemande = $motifDemande;
        return $this;
    }
    public function getMontantPayer(): ?string
    {
        return $this->montantPayer;
    }
    public function setMontantPayer(?string $montantPayer): self
    {
        $this->montantPayer = $montantPayer;
        return $this;
    }
    public function getDevise(): ?string
    {
        return $this->devise;
    }
    public function setDevise(?string $devise): self
    {
        $this->devise = $devise;
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


    public function getNomValidateurFinal()
    {
        return $this->nomValidateurFinal;
    }

    public function setNomValidateurFinal($nomValidateurFinal)
    {
        $this->nomValidateurFinal = $nomValidateurFinal;

        return $this;
    }
}
