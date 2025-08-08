<?php

namespace App\Controller\Traits\da\detail;

use App\Entity\da\DaObservation;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\dw\DwBcAppro;
use App\Entity\dw\DwFacBl;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DaObservationRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\dit\DitRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\dw\DwFactureBonLivraisonRepository;

trait DaDetailAvecDitTrait
{
    use DaDetailTrait;

    //==================================================================================================
    private DitRepository $ditRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DaObservationRepository $daObservationRepository;
    private DwFactureBonLivraisonRepository $dwFacBlRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DossierInterventionAtelierModel $dossierInterventionAtelierModel;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->dwFacBlRepository = $em->getRepository(DwFacBl::class);
        $this->dwBcApproRepository = $em->getRepository(DwBcAppro::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        $this->dossierInterventionAtelierModel = new DossierInterventionAtelierModel;
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
            'BA'    => [
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-ba',
                'fichiers'   => $this->normalizePaths($tab['baPath']),
            ],
            'OR'    => [
                'type'       => 'Ordre de réparation',
                'icon'       => 'fa-solid fa-wrench',
                'colorClass' => 'border-left-or',
                'fichiers'   => $this->normalizePathsForOneFile($tab['orPath'], 'numeroOr'),
            ],
            'BC'    => [
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['bcPath'], 'numeroBc'),
            ],
            'FACBL' => [
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['facblPath'], 'idFacBl'),
            ],
        ];
    }
}
