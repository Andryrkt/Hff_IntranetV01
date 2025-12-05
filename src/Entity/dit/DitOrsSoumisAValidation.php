<?php

namespace App\Entity\dit;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @ORM\Entity(repositoryClass=DitOrsSoumisAValidationRepository::class)
 * @ORM\Table(name="ors_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitOrsSoumisAValidation
{

    public const STATUT_VIDE                       = '';
    public const STATUT_A_RESOUMETTRE_A_VALIDATION = 'A resoumettre à validation';
    public const STATUT_A_VALIDER_CA               = 'A valider chef atelier';
    public const STATUT_A_VALIDER_CLIENT           = 'A valider client interne';
    public const STATUT_A_VALIDER_DT               = 'A valider directeur technique';
    public const STATUT_MODIF_DEMANDE_PAR_CA       = 'Modification demandée par CA';
    public const STATUT_MODIF_DEMANDE_PAR_CLIENT   = 'Modification demandée par client';
    public const STATUT_REFUSE_CA                  = 'Refusé chef atelier';
    public const STATUT_REFUSE_CLIENT              = 'Refusé client interne';
    public const STATUT_REFUSE_DT                  = 'Refusé DT';
    public const STATUT_SOUMIS_A_VALIDATION        = 'Soumis à validation';
    public const STATUT_VALIDE                     = 'Validé';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numeroDIT")
     */
    private ?string $numeroDit = null;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private ?string $numeroOR = '';

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroItv = 0;

    /**
     * @ORM\Column(type="date")
     */
    private  $dateSoumission;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $heureSoumission;

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
     * @ORM\Column(type="string", length=500)
     */
    private ?string $libellelItv = '';

    /**
     * @ORM\Column(type="string", length=3000, nullable=true)
     */
    private ?string $observation = '';



    private $pieceJoint01;

    private $pieceJoint02;

    private $pieceJoint03;

    private $pieceJoint04;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statut;

    /**
     * @ORM\Column(type="integer")
     */
    private $migration;

    /**
     * @ORM\Column(type="boolean", name="piece_faible_activite_achat")
     */
    private $pieceFaibleActiviteAchat;
    //==========================================================================================



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
     * Get the value of dateSoumission
     */
    public function getDateSoumission()
    {
        return $this->dateSoumission;
    }

    /**
     * Set the value of dateSoumission
     *
     * @return  self
     */
    public function setDateSoumission($dateSoumission)
    {
        $this->dateSoumission = $dateSoumission;

        return $this;
    }


    /**
     * Get the value of heureSoumission
     */
    public function getHeureSoumission()
    {
        return $this->heureSoumission;
    }

    /**
     * Set the value of heureSoumission
     *
     * @return  self
     */
    public function setHeureSoumission($heureSoumission)
    {
        $this->heureSoumission = $heureSoumission;

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
     * Get the value of migration
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * Set the value of migration
     *
     * @return  self
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;

        return $this;
    }

    // Comparaison des objets par leur numero d'intervention
    public function estEgalParNumero(DitOrsSoumisAValidation $autre)
    {
        return $this->numeroItv === $autre->numeroItv;
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
     *
     * @return  self
     */
    public function setObservation($observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * Get the value of pieceFaibleActiviteAchat
     */
    public function getPieceFaibleActiviteAchat()
    {
        return $this->pieceFaibleActiviteAchat;
    }

    /**
     * Set the value of pieceFaibleActiviteAchat
     */
    public function setPieceFaibleActiviteAchat($pieceFaibleActiviteAchat): self
    {
        $this->pieceFaibleActiviteAchat = $pieceFaibleActiviteAchat;

        return $this;
    }
}
