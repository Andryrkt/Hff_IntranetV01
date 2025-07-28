<?php

namespace App\Entity\dw;

class DossierInterventionAtelierSearch
{
    private ?\Datetime $dateDebut;

    private ?\DateTime $dateFin;

    private ?string $numDit = '';

    private ?int $numOr = null ;

    private ?string $typeIntervention;

    private ?int $idMateriel = 0;

    private ?string $numParc = '';

    private ?string $numSerie = '';

    private ?string $designation = '';

    /** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */

    /**
     * Get the value of dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set the value of dateDebut
     *
     * @return  self
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get the value of dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set the value of dateFin
     *
     * @return  self
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get the value of numDit
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @return  self
     */
    public function setNumDit($numDit)
    {
        $this->numDit = $numDit;

        return $this;
    }

    /**
     * Get the value of numOr
     */
    public function getNumOr()
    {
        return $this->numOr;
    }

    /**
     * Set the value of numOr
     *
     * @return  self
     */
    public function setNumOr($numOr)
    {
        $this->numOr = $numOr;

        return $this;
    }

    /**
     * Get the value of typeIntervention
     */
    public function getTypeIntervention()
    {
        return $this->typeIntervention;
    }

    /**
     * Set the value of typeIntervention
     *
     * @return  self
     */
    public function setTypeIntervention($typeIntervention)
    {
        $this->typeIntervention = $typeIntervention;

        return $this;
    }

    /**
     * Get the value of idMateriel
     */
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    /**
     * Set the value of idMateriel
     *
     * @return  self
     */
    public function setIdMateriel($idMateriel)
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    /**
     * Get the value of numParc
     */
    public function getNumParc()
    {
        return $this->numParc;
    }

    /**
     * Set the value of numParc
     *
     * @return  self
     */
    public function setNumParc($numParc)
    {
        $this->numParc = $numParc;

        return $this;
    }

    /**
     * Get the value of numSerie
     */
    public function getNumSerie()
    {
        return $this->numSerie;
    }

    /**
     * Set the value of numSerie
     *
     * @return  self
     */
    public function setNumSerie($numSerie)
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    /**
     * Get the value of designation
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Set the value of designation
     *
     * @return  self
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }
}
