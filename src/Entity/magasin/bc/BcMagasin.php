<?php

namespace App\Entity\magasin\bc;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\magasin\bc\BcMagasinRepository;

/**
 * @ORM\Entity(repositoryClass=BcMagasinRepository::class)
 * @ORM\Table(name="bc_client_soumis_neg")
 * @ORM\HasLifecycleCallbacks
 */
class BcMagasin
{
    use DateTrait;

    public const STATUT_SOUMIS_VALIDATION = 'Soumis à validation';
    public const STATUT_EN_ATTENTE_BC = 'En attente BC';
    public const STATUT_VALIDER = 'Validé';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numero_devis", nullable=false)
     *
     * @var string
     */
    private string $numeroDevis;

    /**
     * @ORM\Column(type="string", length=50, name="numero_bc", nullable=false)
     *
     * @var string
     */
    private string $numeroBc;

    /**
     * @ORM\Column(type="float", name="montant_devis", nullable=false)
     *
     * @var float
     */
    private float $montantDevis = 0.00;

    /**
     * @ORM\Column(type="float", name="montant_bc", nullable=false)
     *
     * @var float
     */
    private float $montantBc = 0.00;


    /**
     * @ORM\Column(type="integer", name="numero_version", nullable=false)
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="string", length=100, name="statut_bc", nullable=true)
     *
     * @var string|null
     */
    private ?string $statutBc = '';

    /**
     * @ORM\Column(type="string", length=500, name="observations", nullable=true)
     *
     * @var string|null
     */
    private ?string $observation = '';

    /**
     * @ORM\Column(type="string", length=100, name="utilisateur", nullable=false)
     *
     * @var string
     */
    private string $utilisateur = '';

    /**
     * @ORM\Column(type="datetime", name="date_bc", nullable=true)
     */
    private $dateBc = null;

    private $pieceJoint01;

    private $pieceJoint2;



    /** =========================================
     * GETTERS & SETTERS
     *============================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDevis
     *
     * @return  string
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @param  string  $numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis(string $numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }

    /**
     * Get the value of numeroBc
     *
     * @return  string
     */
    public function getNumeroBc()
    {
        return $this->numeroBc;
    }

    /**
     * Set the value of numeroBc
     *
     * @param  string  $numeroBc
     *
     * @return  self
     */
    public function setNumeroBc(string $numeroBc)
    {
        $this->numeroBc = $numeroBc;

        return $this;
    }

    /**
     * Get the value of montantDevis
     *
     * @return  float
     */
    public function getMontantDevis()
    {
        return $this->montantDevis;
    }

    /**
     * Set the value of montantDevis
     *
     * @param  float  $montantDevis
     *
     * @return  self
     */
    public function setMontantDevis(float $montantDevis)
    {
        $this->montantDevis = $montantDevis;

        return $this;
    }

    /**
     * Get the value of montantBc
     *
     * @return  float
     */
    public function getMontantBc()
    {
        return $this->montantBc;
    }

    /**
     * Set the value of montantBc
     *
     * @param  float  $montantBc
     *
     * @return  self
     */
    public function setMontantBc(float $montantBc)
    {
        $this->montantBc = $montantBc;

        return $this;
    }

    /**
     * Get the value of numeroVersion
     */
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     *
     * @return  self
     */
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }

    /**
     * Get the value of statutBc
     *
     * @return  string|null
     */
    public function getStatutBc()
    {
        return $this->statutBc;
    }

    /**
     * Set the value of statutBc
     *
     * @param  string|null  $statutBc
     *
     * @return  self
     */
    public function setStatutBc($statutBc)
    {
        $this->statutBc = $statutBc;

        return $this;
    }

    /**
     * Get the value of observation
     *
     * @return  string|null
     */
    public function getObservation()
    {
        return $this->observation;
    }

    /**
     * Set the value of observation
     *
     * @param  string|null  $observation
     *
     * @return  self
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of utilisateur
     *
     * @return  string
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @param  string  $utilisateur
     *
     * @return  self
     */
    public function setUtilisateur(string $utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }


    /**
     * Get the value of dateBc
     */
    public function getDateBc()
    {
        return $this->dateBc;
    }

    /**
     * Set the value of dateBc
     */
    public function setDateBc($dateBc): self
    {
        $this->dateBc = $dateBc;

        return $this;
    }

    /**
     * Get the value of pieceJoint01
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJoint01
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }

    /**
     * Get the value of pieceJoint2
     */
    public function getPieceJoint2()
    {
        return $this->pieceJoint2;
    }

    /**
     * Set the value of pieceJoint2
     *
     * @return  self
     */
    public function setPieceJoint2($pieceJoint2)
    {
        $this->pieceJoint2 = $pieceJoint2;

        return $this;
    }
}
