<?php

namespace App\Controller\Traits\da\proposition;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaPropositionDirectTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================
}
