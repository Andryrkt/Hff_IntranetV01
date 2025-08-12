<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

    /** 
     * Méthode pour envoyer une email de modifications pour une DA directe
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailModificationDaDirect(DemandeAppro $demandeAppro, array $tab)
    {
        $this->emailDaService->envoyerMailModificationDaDirect($demandeAppro, $tab);
    }
}
