<?php

namespace App\Entity\da;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DemandeApproLRCollection
{
    private Collection $DALR;

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
