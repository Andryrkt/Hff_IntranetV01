<?php

namespace App\Entity\Traits;

trait AgenceServiceEmetteurTrait
{

    private $agenceEmetteur;


    private $serviceEmetteur;



    public function getAgenceEmetteur(): ?string
    {
        return $this->agenceEmetteur;
    }

    public function setAgenceEmetteur(string $agenceEmetteur): self
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    public function getServiceEmetteur(): ?string
    {
        return $this->serviceEmetteur;
    }

    public function setServiceEmetteur(string $serviceEmetteur): self
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }
}
