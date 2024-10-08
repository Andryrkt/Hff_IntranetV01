<?php

namespace App\Entity\dit;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dit\DitRiSoumisAValidationRepository;


/**
 * @ORM\Entity(repositoryClass=DitRiSoumisAValidationRepository::class)
 * @ORM\Table(name="ri_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitRiSoumisAValidation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=11, name="numero_dit")
     */
    private ?string $numeroDit = null;

    /**
     * @ORM\Column(type="string", length=8, name="numero_or")
     */
    private string $numeroOR;

    /**
     * @ORM\Column(type="date", name="date_soumission")
     */
    private  $dateSoumission;

    /**
     * @ORM\Column(type="string", length=5, name="heureSoumission")
     */
    private $heureSoumission;

    /**
     * @ORM\Column(type="integer", name="numero_soumission")
     */
    private int $numeroSoumission = 0;


    /**
     * @ORM\Column(type="string", length=50, name="statut")
     *
     * @var string
     */
    private ?string $statut = "";

    
    private $pieceJoint01;

    private $pieceJoint02;
    
    private $pieceJoint03;
    
    private $pieceJoint04;

/** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */
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
     * Get the value of numeroSoumission
     */ 
    public function getNumeroSoumission()
    {
        return $this->numeroSoumission;
    }

    /**
     * Set the value of numeroSoumission
     *
     * @return  self
     */ 
    public function setNumeroSoumission($numeroSoumission)
    {
        $this->numeroSoumission = $numeroSoumission;

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
     */ 
    public function setStatut($statut): self
    {
        $this->statut = $statut;

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

}