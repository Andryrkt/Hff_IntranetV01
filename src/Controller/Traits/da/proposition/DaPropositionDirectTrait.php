<?php

namespace App\Controller\Traits\da\proposition;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaPropositionDirectTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

    /** 
     * Méthode pour envoyer une email de propositions pour une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    private function envoyerMailPropositionDaDirect(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailPropositionDaDirect($demandeAppro, $tab);
    }
}
