<?php

namespace App\Entity\Traits;

trait QuantiteDitTrait
{
    private $quantiteDemander = 0;

    private $quantiteReserver = 0;
    
    private $quantiteLivree = 0;

    private $quantiteReliquat = 0;

    private $statutAchatPiece = '';
    
    private $statutAchatLocaux = '';

    /**
     * Get the value of quantiteDemander
     */ 
    public function getQuantiteDemander()
    {
        return $this->quantiteDemander;
    }

    /**
     * Set the value of quantiteDemander
     *
     * @return  self
     */ 
    public function setQuantiteDemander($quantiteDemander)
    {
        $this->quantiteDemander = $quantiteDemander;

        return $this;
    }

    /**
     * Get the value of quantiteReserver
     */ 
    public function getQuantiteReserver()
    {
        return $this->quantiteReserver;
    }

    /**
     * Set the value of quantiteReserver
     *
     * @return  self
     */ 
    public function setQuantiteReserver($quantiteReserver)
    {
        $this->quantiteReserver = $quantiteReserver;

        return $this;
    }

    /**
     * Get the value of quantiteLivree
     */ 
    public function getQuantiteLivree()
    {
        return $this->quantiteLivree;
    }

    /**
     * Set the value of quantiteLivree
     *
     * @return  self
     */ 
    public function setQuantiteLivree($quantiteLivree)
    {
        $this->quantiteLivree = $quantiteLivree;

        return $this;
    }

    /**
     * Get the value of quantiteReliquat
     */ 
    public function getQuantiteReliquat()
    {
        return $this->quantiteReliquat;
    }

    /**
     * Set the value of quantiteReliquat
     *
     * @return  self
     */ 
    public function setQuantiteReliquat($quantiteReliquat)
    {
        $this->quantiteReliquat = $quantiteReliquat;

        return $this;
    }

    /**
     * Get the value of statutAchatPiece
     */ 
    public function getStatutAchatPiece()
    {
        return $this->statutAchatPiece;
    }

    /**
     * Set the value of statutAchatPiece
     *
     * @return  self
     */ 
    public function setStatutAchatPiece($statutAchatPiece)
    {
        $this->statutAchatPiece = $statutAchatPiece;

        return $this;
    }

    /**
     * Get the value of statutAchatLocaux
     */ 
    public function getStatutAchatLocaux()
    {
        return $this->statutAchatLocaux;
    }

    /**
     * Set the value of statutAchatLocaux
     *
     * @return  self
     */ 
    public function setStatutAchatLocaux($statutAchatLocaux)
    {
        $this->statutAchatLocaux = $statutAchatLocaux;

        return $this;
    }
}
