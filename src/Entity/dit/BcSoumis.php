<?php

namespace App\Entity\dit;

use App\Repository\dit\BcSoumisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BcSoumisRepository::class)
 * @ORM\Table(name="bc_soumis")
 */
class BcSoumis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11, name="numeroDit")
     *
     * @var string
     */
    private string $numDit;

    /**
     * @ORM\Column(type="string", length=8, name="numeroDevis")
     *
     * @var string
     */
    private string $numDevis;

    /**
     * @ORM\Column(type="string", length=15, name="numeroBc")
     *
     * @var string
     */
    private string $numBc;

    /**
     * @ORM\Column(type="integer", name="numeroVersion")
     *
     * @var int
     */
    private int $numVersion = 0;

    /**
     * @ORM\Column(type="date", name="dateBc")
     */
    private $dateBc;

    /**
     * @ORM\Column(type="date", name="dateDevis")
     */
    private $dateDevis;

    /**
     * @ORM\Column(type="float", scale="2")
     */
    private ?float $montantDevis = 0.00;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateHeureSoumission;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomFichier;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $statut;


    /**==============================================================================
     * GETTERS & SETTERS
     *===============================================================================*/

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of numDit
     *
     * @return  string
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @param  string  $numDit
     *
     * @return  self
     */
    public function setNumDit(string $numDit)
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of numDevis
     *
     * @return  string
     */
    public function getNumDevis()
    {
        return $this->numDevis;
    }

    /**
     * Set the value of numDevis
     *
     * @param  string  $numDevis
     *
     * @return  self
     */
    public function setNumDevis(string $numDevis)
    {
        $this->numDevis = $numDevis;

        return $this;
    }

    /**
     * Get the value of numBc
     *
     * @return  string
     */
    public function getNumBc()
    {
        return $this->numBc;
    }

    /**
     * Set the value of numBc
     *
     * @param  string  $numBc
     *
     * @return  self
     */
    public function setNumBc(string $numBc)
    {
        $this->numBc = $numBc;

        return $this;
    }

    /**
     * Get the value of numVersion
     *
     * @return  int
     */
    public function getNumVersion()
    {
        return $this->numVersion;
    }

    /**
     * Set the value of numVersion
     *
     * @param  int  $numVersion
     *
     * @return  self
     */
    public function setNumVersion($numVersion)
    {
        $this->numVersion = $numVersion;

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
     *
     * @return  self
     */
    public function setDateBc($dateBc)
    {
        $this->dateBc = $dateBc;

        return $this;
    }

    /**
     * Get the value of dateDevis
     */
    public function getDateDevis()
    {
        return $this->dateDevis;
    }

    /**
     * Set the value of dateDevis
     *
     * @return  self
     */
    public function setDateDevis($dateDevis)
    {
        $this->dateDevis = $dateDevis;

        return $this;
    }

    /**
     * Get the value of montantDevis
     */
    public function getMontantDevis()
    {
        return $this->montantDevis;
    }

    /**
     * Set the value of montantDevis
     *
     * @return  self
     */
    public function setMontantDevis($montantDevis)
    {
        $this->montantDevis = $montantDevis;

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
     * Get the value of nomFichier
     */
    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    /**
     * Set the value of nomFichier
     *
     * @return  self
     */
    public function setNomFichier($nomFichier)
    {
        $this->nomFichier = $nomFichier;

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
}
