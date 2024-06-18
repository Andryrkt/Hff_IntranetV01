<?php

namespace App\Traits;

use App\Entity\Agence;
use App\Entity\Service;

trait AgenceServiceTrait
{
    
    private Agence $agence;

    
    private Service $service;

    public function getAgence(): Agence
    {
        return $this->agence;
    }

    public function setAgence(Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
