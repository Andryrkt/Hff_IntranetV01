<?php

namespace App\Entity\Traits;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;

trait AgenceServiceTrait
{
    
    private ?Agence $agence = null;

    
    private ?Service $service = null;

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
