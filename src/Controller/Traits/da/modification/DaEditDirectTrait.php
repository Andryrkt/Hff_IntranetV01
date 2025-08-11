<?php

namespace App\Controller\Traits\da\modification;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $this->initDaTrait();
    }
    //==================================================================================================

}
