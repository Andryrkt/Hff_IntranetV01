<?php

namespace App\Controller\Traits\da\proposition;

trait DaPropositionAvecDitTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionAvecDitTrait(): void
    {
        $this->initDaTrait();
    }
    //==================================================================================================

}
