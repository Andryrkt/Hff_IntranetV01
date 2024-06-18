<?php

namespace App\Traits;



trait numParcNumSerieTrait
{
    
    private ?string $numParc =null;

    
    private ?string $numSerie = null;


    public function getNumParc(): ?string
    {
        return $this->numParc;
    }

  
    public function setNumParc(?string $numParc): self
    {
        $this->numParc = $numParc;

        return $this;
    }

   
    public function getNumSerie(): ?string
    {
        return $this->numSerie;
    }

    
    public function setNumSerie(?string $numSerie): self
    {
        $this->numSerie = $numSerie;

        return $this;
    }
}


