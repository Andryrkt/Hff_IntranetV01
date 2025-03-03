<?php

namespace App\Entity\Bordereau;

class BordereauSearch {
    private $numInv;
    

    

    /**
     * Get the value of numInv
     */ 
    public function getNumInv()
    {
        return $this->numInv;
    }

    /**
     * Set the value of numInv
     *
     * @return  self
     */ 
    public function setNumInv($numInv)
    {
        $this->numInv = $numInv;

        return $this;
    }
    public function toArray(): array
    {
        return [
            'numInv' => $this->numInv,
        ];
    }
    public function arrayToObjet(array $criteriaTab)
    {
        $this
            ->setNumInv($criteriaTab['numInv'])
        ;
    }

}