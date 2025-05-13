<?php

namespace App\Entity\da;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DemandeApproLRCollection
{
    private Collection $DALR;

    private $Observation;

    private $estValidee = false;

    /**===========================================================================
     * GETTER & SETTER
     *
     *==========================================================================*/

    public function __construct()
    {
        $this->DALR = new ArrayCollection();
    }

    /**
     * Get the value of DALR
     *
     * @return Collection
     */
    public function getDALR(): Collection
    {
        return $this->DALR;
    }

    /**
     * Set the value of DALR
     *
     * @param Collection $DALR
     *
     * @return self
     */
    public function setDALR(Collection $DALR): self
    {
        $this->DALR = $DALR;
        return $this;
    }

    /**
     * Get the value of Observation
     */
    public function getObservation()
    {
        return $this->Observation;
    }

    /**
     * Set the value of Observation
     *
     * @return  self
     */
    public function setObservation($Observation)
    {
        $this->Observation = $Observation;

        return $this;
    }

    public function getEstValidee()
    {
        return $this->estValidee;
    }

    public function setEstValidee($estValidee)
    {
        $this->estValidee = $estValidee;

        return $this;
    }
}
