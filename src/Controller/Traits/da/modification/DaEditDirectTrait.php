<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

}
