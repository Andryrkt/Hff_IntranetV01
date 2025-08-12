<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
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

    /** 
     * Méthode pour envoyer une email de propositions pour une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    private function envoyerMailModificationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailModificationDaAvecDit($demandeAppro, $tab);
    }
}
