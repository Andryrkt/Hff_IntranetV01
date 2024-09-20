<?php

namespace App\Entity\dit;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

/**
 * @ORM\Entity(repositoryClass=DitOrsSoumisAValidationRepository::class)
 * @ORM\Table(name="ors_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitOrsSoumisAValidation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

   
    private ?string $numeroDit = null;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private string $numeroOR;

    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroItv;

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
    private int $nombreLigneItv;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantItv;


    /**
     * @ORM\Column(type="integer")
     */
    private int $numeroVersion = 0;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantPiece;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantMo;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantAchatLocaux;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantFraisDivers;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private float $montantLubrifiants;
    
    /**
     * @ORM\Column(type="string", length=500)
     */
    private string $libellelItv;

    /**
     * @ORM\OneToMany(targetEntity=DitHistoriqueOperationDocument::class, mappedBy="idOrSoumisAValidation")
     */
    private $ditHistoriqueOperationDoc;


    private $pieceJoint01;
    //==========================================================================================
    

    public function __construct()
    {
        $this->ditHistoriqueOperationDoc = new ArrayCollection();
    }

    
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
     * Get the value of demandeIntervention
     */ 
    public function getDitHistoriqueOperationDoc()
    {
        return $this->ditHistoriqueOperationDoc;
    }

    public function addDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if (!$this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc[] = $ditHistoriqueOperationDoc;
            $ditHistoriqueOperationDoc->setIdOrSoumisAValidation($this);
        }

        return $this;
    }

    public function removeDitHistoriqueOperationDoc(DitHistoriqueOperationDocument $ditHistoriqueOperationDoc): self
    {
        if ($this->ditHistoriqueOperationDoc->contains($ditHistoriqueOperationDoc)) {
            $this->ditHistoriqueOperationDoc->removeElement($ditHistoriqueOperationDoc);
            if ($ditHistoriqueOperationDoc->getIdOrSoumisAValidation() === $this) {
                $ditHistoriqueOperationDoc->setIdOrSoumisAValidation(null);
            }
        }
        
        return $this;
    }

    public function setDitHistoriqueOperationDoc($ditHistoriqueOperationDoc)
    {
        $this->ditHistoriqueOperationDoc = $ditHistoriqueOperationDoc;

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

    

   
}