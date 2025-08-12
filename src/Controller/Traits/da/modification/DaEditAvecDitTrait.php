<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DaObservation;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DaObservationRepository;

trait DaEditAvecDitTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

}
