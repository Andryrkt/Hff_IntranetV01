<?php

namespace App\Controller\Traits\da;

use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaPropositionTrait
{
    use EntityManagerAwareTrait;

    //=====================================================================================
    private DaObservationRepository $daObservationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionTrait(): void
    {
        $em = $this->getEntityManager();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //=====================================================================================
}
