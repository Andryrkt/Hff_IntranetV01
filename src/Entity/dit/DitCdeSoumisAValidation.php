<?php

namespace App\Entity\dit;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\dit\DitCdeSoumisAValidationRepository;



/**
 * @ORM\Entity(repositoryClass=DitCdeSoumisAValidationRepository::class)
 * @ORM\Table(name="cde_soumis_a_validation")
 * @ORM\HasLifecycleCallbacks
 */
class DitCdeSoumisAValidation
{    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="numero_cde")
     */
    private int $numeroCde = 0;

    /**
     * @ORM\Column(type="string", name="utilisateur")
     */
    private string $utilisateur;

    /**
     * @ORM\Column(type="integer", name="numero_version")
     */
    private int $numeroVersion;

    /**
     * @ORM\Column(type="json", name="numero_or")
     */
    private $numeroOrs = [];

    /**
     * @ORM\Column(type="string", name="statut")
     */
    private string $statut;

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
     * Get the value of numeroOrs
     */ 
    public function getNumeroOrs()
    {
        return $this->numeroOrs;
    }

    /**
     * Set the value of numeroOrs
     *
     * @return  self
     */ 
    public function setNumeroOrs($numeroOrs)
    {
        $this->numeroOrs = $numeroOrs;

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