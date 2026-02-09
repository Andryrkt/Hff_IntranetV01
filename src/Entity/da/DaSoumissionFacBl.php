<?php

namespace App\Entity\da;

use App\Constants\da\ddp\BonApayerConstants;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\da\DaSoumissionFacBlRepository;

/**
 * @ORM\Entity(repositoryClass=DaSoumissionFacBlRepository::class)
 * @ORM\Table(name="da_soumission_facture_bl")
 * @ORM\HasLifecycleCallbacks
 */
class DaSoumissionFacBl
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, name="numero_demande_appro")
     */
    private ?string $numeroDemandeAppro;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_dit")
     */
    private ?string $numeroDemandeDit;

    /**
     * @ORM\Column(type="string", length=11, name="numero_or")
     */
    private ?string $numeroOR;

    /**
     * @ORM\Column(type="string", length=11, name="numero_cde")
     */
    private ?string $numeroCde;

    /**
     * @ORM\Column(type="string", length=10, name="numero_livraison", nullable=true)
     */
    private ?string $numLiv;

    /**
     * @ORM\Column(type="string", length=255, name="reference_bl_facture", nullable=true)
     */
    private ?string $refBlFac;

    /**
     * @ORM\Column(type="datetime", name="date_bl_facture", nullable=true)
     */
    private $dateBlFac;

    /**
     * @ORM\Column(type="datetime", name="date_cloture_liv", nullable=true)
     */
    private $dateClotLiv;

    /**
     * @ORM\Column(type="string", length=100, name="statut")
     */
    private ?string $statut;

    /**
     * @ORM\Column(type="string", length=255, name="piece_joint1")
     */
    private $pieceJoint1;

    /**
     * @ORM\Column(type="string", length=255, name="utilisateur")
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private $numeroVersion;

    /**
     * @ORM\Column(type="string", length=11, name="numero_bap")
     */
    private $numeroBap;

    /**
     * @ORM\Column(type="string", length=100, name="statut_bap")
     */
    private $statutBap;

    /**
     * @ORM\Column(type="datetime", name="date_soumission_compta")
     */
    private $dateSoumissionCompta;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, name="montant_bl_facture")
     */
    private $montantBlFacture;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, name="montant_reception_ips")
     */
    private $montantReceptionIps;

    /**
     * @ORM\Column(type="string", length=11, name="numero_demande_paiement")
     */
    private $numeroDemandePaiement;

    /** 
     * @ORM\Column(type="datetime", name="date_statut_bap", nullable=true)
     */
    private $dateStatutBap;

    /**
     * @ORM\Column(type="integer", name="numero_fournisseur", nullable=true)
     */
    private $numeroFournisseur;

    /**
     * @ORM\Column(type="string", length=255, name="nom_fournisseur", nullable=true)
     */
    private $nomFournisseur;

    /**
     * @ORM\Column(type="string", length=255, name="numero_facture_fournisseur", nullable=true)
     */
    private $NumeroFactureFournisseur;


    /**
     * @ORM\Column(type="boolean", name="est_facture_reappro", nullable=true, options={"default" : 0})
     */
    private bool $estFactureReappro = false;

    /**
     * @ORM\Column(type="string", length=8, name="numero_facture_reappro", nullable=true)
     */
    private ?string $numeroFactureReappro = null;


    private $pieceJoint2;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDemandeAppro
     */
    public function getNumeroDemandeAppro()
    {
        return $this->numeroDemandeAppro;
    }

    /**
     * Set the value of numeroDemandeAppro
     *
     * @return  self
     */
    public function setNumeroDemandeAppro($numeroDemandeAppro)
    {
        $this->numeroDemandeAppro = $numeroDemandeAppro;

        return $this;
    }

    /**
     * Get the value of numeroDemandeDit
     */
    public function getNumeroDemandeDit()
    {
        return $this->numeroDemandeDit;
    }

    /**
     * Set the value of numeroDemandeDit
     *
     * @return  self
     */
    public function setNumeroDemandeDit($numeroDemandeDit)
    {
        $this->numeroDemandeDit = $numeroDemandeDit;

        return $this;
    }

    /**
     * Get the value of numeroOR
     */
    public function getNumeroOR()
    {
        return $this->numeroOR;
    }

    /**
     * Set the value of numeroOR
     *
     * @return  self
     */
    public function setNumeroOR($numeroOR)
    {
        $this->numeroOR = $numeroOR;

        return $this;
    }

    /**
     * Get the value of numeroCde
     */
    public function getNumeroCde()
    {
        return $this->numeroCde;
    }

    /**
     * Set the value of numeroCde
     *
     * @return  self
     */
    public function setNumeroCde($numeroCde)
    {
        $this->numeroCde = $numeroCde;

        return $this;
    }

    /**
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of numLiv
     */
    public function getNumLiv()
    {
        return $this->numLiv;
    }

    /**
     * Set the value of numLiv
     *
     * @return  self
     */
    public function setNumLiv($numLiv)
    {
        $this->numLiv = $numLiv;

        return $this;
    }

    /**
     * Get the value of refBlFac
     */
    public function getRefBlFac()
    {
        return $this->refBlFac;
    }

    /**
     * Set the value of refBlFac
     *
     * @return  self
     */
    public function setRefBlFac($refBlFac)
    {
        $this->refBlFac = $refBlFac;

        return $this;
    }

    /**
     * Get the value of dateBlFac
     */
    public function getDateBlFac()
    {
        return $this->dateBlFac;
    }

    /**
     * Set the value of dateBlFac
     *
     * @return  self
     */
    public function setDateBlFac($dateBlFac)
    {
        $this->dateBlFac = $dateBlFac;

        return $this;
    }

    /**
     * Get the value of dateClotLiv
     */
    public function getDateClotLiv()
    {
        return $this->dateClotLiv;
    }

    /**
     * Set the value of dateClotLiv
     *
     * @return  self
     */
    public function setDateClotLiv($dateClotLiv)
    {
        $this->dateClotLiv = $dateClotLiv;

        return $this;
    }

    /**
     * Get the value of pieceJoint1
     */
    public function getPieceJoint1()
    {
        return $this->pieceJoint1;
    }

    /**
     * Set the value of pieceJoint1
     *
     * @return  self
     */
    public function setPieceJoint1($pieceJoint1)
    {
        $this->pieceJoint1 = $pieceJoint1;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    public function setNumeroVersion($numeroVersion): self
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }


    /**
     * Get the value of numeroBap
     */
    public function getNumeroBap()
    {
        return $this->numeroBap;
    }

    /**
     * Set the value of numeroBap
     */
    public function setNumeroBap($numeroBap): self
    {
        $this->numeroBap = $numeroBap;

        return $this;
    }

    /**
     * Get the value of statutBap
     */
    public function getStatutBap()
    {
        return $this->statutBap;
    }

    /**
     * Set the value of statutBap
     */
    public function setStatutBap($statutBap): self
    {
        $this->statutBap = $statutBap;

        return $this;
    }

    /**
     * Get the value of dateSoumissionCompta
     */
    public function getDateSoumissionCompta()
    {
        return $this->dateSoumissionCompta;
    }

    /**
     * Set the value of dateSoumissionCompta
     */
    public function setDateSoumissionCompta($dateSoumissionCompta): self
    {
        $this->dateSoumissionCompta = $dateSoumissionCompta;

        return $this;
    }

    /**
     * Get the value of montantBlFacture
     */
    public function getMontantBlFacture()
    {
        return $this->montantBlFacture;
    }

    /**
     * Set the value of montantBlFacture
     */
    public function setMontantBlFacture($montantBlFacture): self
    {
        $this->montantBlFacture = $montantBlFacture;

        return $this;
    }

    /**
     * Get the value of montantReceptionIps
     */
    public function getMontantReceptionIps()
    {
        return $this->montantReceptionIps;
    }

    /**
     * Set the value of montantReceptionIps
     */
    public function setMontantReceptionIps($montantReceptionIps): self
    {
        $this->montantReceptionIps = $montantReceptionIps;

        return $this;
    }

    /**
     * Get the value of numeroDemandePaiement
     */
    public function getNumeroDemandePaiement()
    {
        return $this->numeroDemandePaiement;
    }

    /**
     * Set the value of numeroDemandePaiement
     */
    public function setNumeroDemandePaiement($numeroDemandePaiement): self
    {
        $this->numeroDemandePaiement = $numeroDemandePaiement;

        return $this;
    }

    /**
     * Get the value of dateStatutBap
     */
    public function getDateStatutBap()
    {
        return $this->dateStatutBap;
    }

    /**
     * Set the value of dateStatutBap
     */
    public function setDateStatutBap($dateStatutBap): self
    {
        $this->dateStatutBap = $dateStatutBap;

        return $this;
    }

    /**
     * Get the value of numeroFournisseur
     */
    public function getNumeroFournisseur()
    {
        return $this->numeroFournisseur;
    }

    /**
     * Set the value of numeroFournisseur
     */
    public function setNumeroFournisseur($numeroFournisseur): self
    {
        $this->numeroFournisseur = $numeroFournisseur;

        return $this;
    }

    /**
     * Get the value of nomFournisseur
     */
    public function getNomFournisseur()
    {
        return $this->nomFournisseur;
    }

    /**
     * Set the value of nomFournisseur
     */
    public function setNomFournisseur($nomFournisseur): self
    {
        $this->nomFournisseur = $nomFournisseur;

        return $this;
    }

    public function getNumeroFactureFournisseur()
    {
        return $this->NumeroFactureFournisseur;
    }

    public function setNumeroFactureFournisseur($NumeroFactureFournisseur): self
    {
        $this->NumeroFactureFournisseur = $NumeroFactureFournisseur;

        return $this;
    }



    /**
     * Retourne la classe CSS appropriée pour le statut de la demande
     * Utilise StatutDomConstants pour centraliser la logique
     * 
     * @return string
     */
    public function getStatutCssClass(): string
    {
        if (!$this->statutBap) {
            return '';
        }

        return BonApayerConstants::getCssClass($this->statutBap);
    }

    /**
     * Get the value of estFactureReappro
     */
    public function isEstFactureReappro(): bool
    {
        return $this->estFactureReappro;
    }

    /**
     * Set the value of estFactureReappro
     */
    public function setEstFactureReappro(bool $estFactureReappro): self
    {
        $this->estFactureReappro = $estFactureReappro;

        return $this;
    }

    /**
     * Get the value of numeroFactureReappro
     */
    public function getNumeroFactureReappro(): ?string
    {
        return $this->numeroFactureReappro;
    }

    /**
     * Set the value of numeroFactureReappro
     */
    public function setNumeroFactureReappro(?string $numeroFactureReappro): self
    {
        $this->numeroFactureReappro = $numeroFactureReappro;

        return $this;
    }

    /**
     * Get the value of nomFicheBc
     */
    public function getPieceJoint2()
    {
        return $this->pieceJoint2;
    }

    /**
     * Set the value of nomFicheBc
     *
     * @return  self
     */
    public function setPieceJoint2($pieceJoint2)
    {
        $this->pieceJoint2 = $pieceJoint2;

        return $this;
    }
}
