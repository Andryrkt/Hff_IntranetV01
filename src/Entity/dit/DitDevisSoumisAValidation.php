<?php

namespace App\Entity\dit;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\dit\DitDevisSoumisAValidationRepository;

/**
 * @ORM\Entity(repositoryClass=DitDevisSoumisAValidationRepository::class)
 * @ORM\Table(name="devis_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitDevisSoumisAValidation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private ?string $numeroDit = "";

    /**
     * @ORM\Column(type="string", length=8)
     */
    private ?string $numeroDevis = "";

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroItv = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private  $dateHeureSoumission;


    /**
     * @ORM\Column(type="integer")
     */
    private ?int $nombreLigneItv = 0;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantItv = 0.00;


    /**
     * @ORM\Column(type="integer")
     */
    private ?int $numeroVersion = 0;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantPiece = 0.00;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantMo = 0.00;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantAchatLocaux = 0.00;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantFraisDivers = 0.00;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantLubrifiants = 0.00;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantForfait = 0.00;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private ?string $libellelItv = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $statut;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $natureOperation;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $type;

    private $pieceJoint01;

    private $pieceJoint02;

    private $pieceJoint03;

    private $pieceJoint04;

    private $nomClient;

    private $numeroClient;

    private $objetDit;

    /**
     * @ORM\Column(type="string", length="15")
     *
     * @var string
     */
    private string $devisVenteOuForfait;

    /**
     * @ORM\Column(type="string", length="10")
     */
    private ?string $devise = '';

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantVente = 0.00;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private int $nombreLignePiece = 0;
    /** ========================================================================================== 
     * GETTERS & SETTERS
     *==========================================================================================*/



    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numeroDit
     */
    public function getNumeroDit()
    {
        return $this->numeroDit;
    }

    /**
     * Set the value of numeroDit
     *
     * @return  self
     */
    public function setNumeroDit($numeroDit)
    {
        $this->numeroDit = $numeroDit;

        return $this;
    }

    /**
     * Get the value of numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }


    /**
     * Get the value of numeroItv
     */
    public function getNumeroItv()
    {
        return $this->numeroItv;
    }

    /**
     * Set the value of numeroItv
     *
     * @return  self
     */
    public function setNumeroItv($numeroItv)
    {
        $this->numeroItv = $numeroItv;

        return $this;
    }

    /**
     * Get the value of dateHeureSoumission
     */
    public function getDateHeureSoumission()
    {
        return $this->dateHeureSoumission;
    }

    /**
     * Set the value of dateHeureSoumission
     *
     * @return  self
     */
    public function setDateHeureSoumission($dateHeureSoumission)
    {
        $this->dateHeureSoumission = $dateHeureSoumission;

        return $this;
    }

    /**
     * Get the value of nombrePieceItv
     */
    public function getNombreLigneItv()
    {
        return $this->nombreLigneItv;
    }

    /**
     * Set the value of nombrePieceItv
     *
     * @return  self
     */
    public function setNombreLigneItv($nombreLigneItv)
    {
        $this->nombreLigneItv = $nombreLigneItv;

        return $this;
    }

    /**
     * Get the value of montantItv
     */
    public function getMontantItv()
    {
        return $this->montantItv;
    }

    /**
     * Set the value of montantItv
     *
     * @return  self
     */
    public function setMontantItv($montantItv)
    {
        $montantItv = $montantItv === null ? 0.00 : $montantItv;
        $this->montantItv = $montantItv;

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
     * Get the value of montantPiece
     */
    public function getMontantPiece()
    {
        return $this->montantPiece;
    }

    /**
     * Set the value of montantPiece
     *
     * @return  self
     */
    public function setMontantPiece($montantPiece)
    {
        $montantPiece = $montantPiece === null ? 0.00 : $montantPiece;
        $this->montantPiece = $montantPiece;

        return $this;
    }

    /**
     * Get the value of montantMo
     */
    public function getMontantMo()
    {
        return $this->montantMo;
    }

    /**
     * Set the value of montantMo
     *
     * @return  self
     */
    public function setMontantMo($montantMo)
    {
        $montantMo = $montantMo === null ? 0.00 : $montantMo;
        $this->montantMo = $montantMo;

        return $this;
    }

    /**
     * Get the value of montantAchatLocaux
     */
    public function getMontantAchatLocaux()
    {
        return $this->montantAchatLocaux;
    }

    /**
     * Set the value of montantAchatLocaux
     *
     * @return  self
     */
    public function setMontantAchatLocaux($montantAchatLocaux)
    {
        $montantAchatLocaux = $montantAchatLocaux === null ? 0.00 : $montantAchatLocaux;
        $this->montantAchatLocaux = $montantAchatLocaux;

        return $this;
    }

    /**
     * Get the value of montantFraisDivers
     */
    public function getMontantFraisDivers()
    {
        return $this->montantFraisDivers;
    }

    /**
     * Set the value of montantFraisDivers
     *
     * @return  self
     */
    public function setMontantFraisDivers($montantFraisDivers)
    {
        $montantFraisDivers = $montantFraisDivers === null ? 0.00 : $montantFraisDivers;
        $this->montantFraisDivers = $montantFraisDivers;

        return $this;
    }

    /**
     * Get the value of montantLubrifiants
     */
    public function getMontantLubrifiants()
    {
        return $this->montantLubrifiants;
    }

    /**
     * Set the value of montantLubrifiants
     *
     * @return  self
     */
    public function setMontantLubrifiants($montantLubrifiants)
    {
        $montantLubrifiants = $montantLubrifiants === null ? 0.00 : $montantLubrifiants;
        $this->montantLubrifiants = $montantLubrifiants;

        return $this;
    }

    /**
     * Get the value of montantForfait
     */
    public function getMontantForfait()
    {
        return $this->montantForfait;
    }

    /**
     * Set the value of montantForfait
     *
     * @return  self
     */
    public function setMontantForfait($montantForfait)
    {
        $this->montantForfait = $montantForfait;

        return $this;
    }

    /**
     * Get the value of libellelItv
     */
    public function getLibellelItv()
    {
        return $this->libellelItv;
    }

    /**
     * Set the value of libellelItv
     *
     * @return  self
     */
    public function setLibellelItv($libellelItv)
    {
        $this->libellelItv = $libellelItv;

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
     * Get the value of natureOperation
     */
    public function getNatureOperation()
    {
        return $this->natureOperation;
    }

    /**
     * Set the value of natureOperation
     *
     * @return  self
     */
    public function setNatureOperation($natureOperation)
    {
        $this->natureOperation = $natureOperation;

        return $this;
    }

    /**
     * Get the value of file
     */
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }



    /**
     * Get the value of pieceJoint02
     */
    public function getPieceJoint02()
    {
        return $this->pieceJoint02;
    }

    /**
     * Set the value of pieceJoint02
     *
     * @return  self
     */
    public function setPieceJoint02($pieceJoint02)
    {
        $this->pieceJoint02 = $pieceJoint02;

        return $this;
    }

    /**
     * Get the value of pieceJoint03
     */
    public function getPieceJoint03()
    {
        return $this->pieceJoint03;
    }

    /**
     * Set the value of pieceJoint03
     *
     * @return  self
     */
    public function setPieceJoint03($pieceJoint03)
    {
        $this->pieceJoint03 = $pieceJoint03;

        return $this;
    }

    /**
     * Get the value of pieceJoint04
     */
    public function getPieceJoint04()
    {
        return $this->pieceJoint04;
    }

    /**
     * Set the value of pieceJoint04
     *
     * @return  self
     */
    public function setPieceJoint04($pieceJoint04)
    {
        $this->pieceJoint04 = $pieceJoint04;

        return $this;
    }


    /**
     * Get the value of nomClient
     */
    public function getNomClient()
    {
        return $this->nomClient;
    }

    /**
     * Set the value of nomClient
     *
     * @return  self
     */
    public function setNomClient($nomClient)
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    /**
     * Get the value of numeroClient
     */
    public function getNumeroClient()
    {
        return $this->numeroClient;
    }

    /**
     * Set the value of numeroClient
     *
     * @return  self
     */
    public function setNumeroClient($numeroClient)
    {
        $this->numeroClient = $numeroClient;

        return $this;
    }

    /**
     * Get the value of objetDit
     */
    public function getObjetDit()
    {
        return $this->objetDit;
    }

    /**
     * Set the value of objetDit
     *
     * @return  self
     */
    public function setObjetDit($objetDit)
    {
        $this->objetDit = $objetDit;

        return $this;
    }

    /**
     * Get the value of devisVenteOuForfait
     *
     * @return  string
     */
    public function getDevisVenteOuForfait()
    {
        return $this->devisVenteOuForfait;
    }

    /**
     * Set the value of devisVenteOuForfait
     *
     * @param  string  $devisVenteOuForfait
     *
     * @return  self
     */
    public function setDevisVenteOuForfait(string $devisVenteOuForfait)
    {
        $this->devisVenteOuForfait = $devisVenteOuForfait;

        return $this;
    }


    /**
     * Get the value of devise
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Set the value of devise
     *
     * @return  self
     */
    public function setDevise($devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get the value of montantVente
     */
    public function getMontantVente()
    {
        return $this->montantVente;
    }

    /**
     * Set the value of montantVente
     *
     * @return  self
     */
    public function setMontantVente($montantVente)
    {
        $this->montantVente = $montantVente;

        return $this;
    }


    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    // Comparaison des objets par leur numero d'intervention
    public function estEgalParNumero(DitDevisSoumisAValidation $autre)
    {
        return $this->numeroItv === $autre->numeroItv;
    }


    /**
     * Get the value of nombreLignePiece
     *
     * @return  integer
     */
    public function getNombreLignePiece()
    {
        return $this->nombreLignePiece;
    }

    /**
     * Set the value of nombreLignePiece
     *
     * @param  integer  $nombreLignePiece
     *
     * @return  self
     */
    public function setNombreLignePiece($nombreLignePiece)
    {
        $this->nombreLignePiece = $nombreLignePiece;

        return $this;
    }
}
