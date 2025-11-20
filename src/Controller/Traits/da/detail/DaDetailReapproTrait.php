<?php

namespace App\Controller\Traits\da\detail;

use App\Entity\da\DaObservation;
use App\Entity\dw\DwDaReappro;
use App\Repository\da\DaObservationRepository;
use App\Repository\dw\DwDaReapproRepository;

trait DaDetailReapproTrait
{
    use DaDetailTrait;

    //==================================================================================================
    private DwDaReapproRepository $dwDaReapproRepository;
    private DaObservationRepository $daObservationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailReapproTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->dwDaReapproRepository   = $em->getRepository(DwDaReappro::class);
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
                'labeltype'  => 'BAI',
                'type'       => "Bon d'achat (Intranet)",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-bai',
                'fichiers'   => $this->normalizePaths($tab['baiPath']),
            ],
            [
                'labeltype'  => 'BAD',
                'type'       => "Bon d'achat (DocuWare)",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-bad',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['badPath'], 'num'),
            ],
        ];
    }
}
