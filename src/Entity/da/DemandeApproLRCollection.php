<?php

namespace App\Entity\da;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


class DemandeApproLRCollection
{
    private Collection $DALR;

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
}
