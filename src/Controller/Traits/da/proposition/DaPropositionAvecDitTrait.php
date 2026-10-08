<?php

namespace App\Controller\Traits\da\proposition;

use App\Model\da\DaModel;

trait DaPropositionAvecDitTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    private DaModel $daModel;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daModel = new DaModel();
    }
    //==================================================================================================

}
