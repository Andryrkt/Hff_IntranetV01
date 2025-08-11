<?php

namespace App\Controller\Traits\da\proposition;

use App\Model\da\DaModel;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaObservationRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

trait DaPropositionAvecDitTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    private DaModel $daModel;
    private DaObservationRepository $daObservationRepository;
    private DitRepository $ditRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daModel = new DaModel();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
    }
    //==================================================================================================

    /** 
     * Méthode pour envoyer une email de propositions pour une DA avec DIT
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    private function envoyerMailPropositionDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $this->emailDaService->envoyerMailPropositionDaAvecDit($demandeAppro, $tab);
    }
}
