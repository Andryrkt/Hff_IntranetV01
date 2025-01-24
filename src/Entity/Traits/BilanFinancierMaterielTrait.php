<?php

namespace App\Entity\Traits;

trait BilanFinancierMaterielTrait
{
 /**
  * Undocumented variable
  *
  * @var float
  */
    private float $coutAcquisition = 0.0;
    
    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $amortissement = 0.0;

    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $valeurNetComptable = 0.0;

    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $chiffreAffaire = 0.0;

    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $chargeLocative = 0.0;

    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $chargeEntretient = 0.0;

    /**
     * Undocumented variable
     *
     * @var float
     */
    private float $resultatExploitation = 0.0;
   
    
    

    
    public function getCoutAcquisition(): float
    {
        return $this->coutAcquisition;
    }

    
    public function setCoutAcquisition(float $coutAcquisition): self
    {
        $this->coutAcquisition = $coutAcquisition;

        return $this;
    }

    
    public function getAmortissement(): float
    {
        return $this->amortissement;
    }

    
    public function setAmortissement(float $amortissement): self
    {
        $this->amortissement = $amortissement;

        return $this;
    }

    
    public function getValeurNetComptable(): float
    {
        return $this->valeurNetComptable = $this->getCoutAcquisition() - $this->getAmortissement();
    }

    
    public function setValeurNetComptable(float $valeurNetComptable)
    {
        $this->valeurNetComptable = $valeurNetComptable;

        return $this;
    }

    
    public function getChiffreAffaire(): float
    {
        return $this->chiffreAffaire;
    }

    
    public function setChiffreAffaire(float $chiffreAffaire): self
    {
        $this->chiffreAffaire = $chiffreAffaire;

        return $this;
    }

    
    public function getChargeLocative(): float
    {
        return $this->chargeLocative;
    }

    
    public function setChargeLocative(float $chargeLocative): self
    {
        $this->chargeLocative = $chargeLocative;

        return $this;
    }

    
    public function getChargeEntretient(): float
    {
        return $this->chargeEntretient;
    }

    
    public function setChargeEntretient(float $chargeEntretient): self
    {
        $this->chargeEntretient = $chargeEntretient;

        return $this;
    }

    
    public function getResultatExploitation(): float
    {
        return $this->resultatExploitation = $this->getChiffreAffaire() - $this->getChargeLocative() - $this->getChargeEntretient();
    }

   
    public function setResultatExploitation(float $resultatExploitation)
    {
        $this->resultatExploitation = $resultatExploitation;

        return $this;
    }
}