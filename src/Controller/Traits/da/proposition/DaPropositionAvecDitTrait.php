<?php

namespace App\Controller\Traits\da\proposition;

use App\Model\da\DaModel;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaObservationRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

trait DaPropositionAvecDitTrait
{
    use DaPropositionTrait;

    //==================================================================================================
    private DaModel $daModel;
    private DaObservationRepository $daObservationRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private $fournisseurs;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaPropositionAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daModel = new DaModel();
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        $this->setAllFournisseurs();
    }
    //==================================================================================================

    /** 
     * Fonctions pour définir les fournisseurs dans le propriété $fournisseur
     */
    private function setAllFournisseurs()
    {
        $fournisseurs = $this->daModel->getAllFournisseur();
        $this->fournisseurs = array_column($fournisseurs, 'numerofournisseur', 'nomfournisseur');
    }
}
