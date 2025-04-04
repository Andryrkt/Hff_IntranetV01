<?php

namespace App\Entity\Bordereau;

class BordereauSearch {
    private $choix;
    private $numInv;
    /**
     * Get the value of choix
     */ 
    public function getChoix()
    {
        return $this->choix;
    }
    
    /**
     * Set the value of choix
     *
     * @return  self
     */ 
    public function setChoix($choix)
    {
        $this->choix = $choix;
        
        return $this;
    }
    public function toArray(): array
    {
        return [
            'choix' => $this->choix,
            'numInv'=>$this->numInv
        ];
    }
    public function arrayToObjet(array $criteriaTab)
    {
        $this
            ->setChoix($criteriaTab['choix'])
            ->setNumInv($criteriaTab['numInv'])
        ;
    }

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
}