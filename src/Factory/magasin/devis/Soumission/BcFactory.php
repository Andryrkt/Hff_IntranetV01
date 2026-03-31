<?php

namespace App\Factory\magasin\devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\BcDto;

class BcFactory
{
    public function create($numeroDevis): BcDto
    {
        $bcDto = new BcDto();
        $bcDto->numeroDevis = $numeroDevis;
        $bcDto->dateCreation = new \DateTime();
        $bcDto->dateModification = new \DateTime();

        return $bcDto;
    }
}
