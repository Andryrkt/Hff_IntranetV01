<?php

namespace App\Controller\Traits\da\detail;

use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaDetailReapproTrait
{
    use DaDetailTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailReapproTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================


    /** 
     * Obtenir tous les fichiers associés à la demande d'approvisionnement
     * 
     * @param array $tab
     */
    private function getAllDAFile($tab): array
    {
        return [
            [
                'labeltype'  => 'BA',
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-ba',
                'fichiers'   => $this->normalizePaths($tab['baPath']),
            ],
        ];
    }
}
