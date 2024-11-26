<?php

namespace App\Entity\dit;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\dit\DitCdeSoumisAValidationRepository;



/**
 * @ORM\Entity(repositoryClass=DitCdeSoumisAValidationRepository::class)
 * TODO : change le nom de la table
 * @ORM\Table(name="ri_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitCdeSoumisAValidation
{
    use DateTrait;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="numero_soumission")
     */
    private int $numeroSoumission = 0;

    private $pieceJoint01;



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