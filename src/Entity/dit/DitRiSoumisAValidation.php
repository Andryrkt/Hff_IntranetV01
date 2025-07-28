<?php

namespace App\Entity\dit;

use App\Repository\dit\DitRiSoumisAValidationRepository;
use Doctrine\ORM\Mapping as ORM;

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
    private $dateSoumission;

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

    /**
     * @ORM\Column(type="integer", name="numeroItv")
     */
    private $numeroItv;

    private $pieceJoint01;

    private $action;

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
     * Get the value of action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the value of action
     *
     * @return  self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }
}
