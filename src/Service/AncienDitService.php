<?php

namespace App\Service;

use App\Controller\Controller;
use App\Entity\AncienDit;

class AncienDitService 
{
    private $em;

    public function __construct()
    {
        $this->em = Controller::getEntity();
    }

    public function recupDesAncienDonnee()
    {
        $data = $this->em->getRepository(AncienDit::class)->findAll();
        return $data;
    }
}
