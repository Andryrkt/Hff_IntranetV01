<?php

namespace App\Controller\Traits\da\proposition;

trait DaPropositionDirectTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionDirectTrait(): void
    {
        $this->initDaTrait();
    }
    //==================================================================================================

}
