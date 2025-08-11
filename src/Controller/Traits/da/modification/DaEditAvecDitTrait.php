<?php

namespace App\Controller\Traits\da\modification;

trait DaEditAvecDitTrait
{
    use DaEditTrait;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditAvecDitTrait(): void
    {
        $this->initDaTrait();
    }
    //==================================================================================================

}
