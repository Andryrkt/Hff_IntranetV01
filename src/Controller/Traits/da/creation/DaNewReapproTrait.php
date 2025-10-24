<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DemandeAppro;
use App\Traits\JoursOuvrablesTrait;

trait DaNewReapproTrait
{
    use DaNewTrait, JoursOuvrablesTrait;

    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewReapproTrait(): void
    {
        $this->initDaTrait();
    }
    //=====================================================================================

    /** 
     * Fonction pour initialiser une demande appro réappro
     * 
     * @return DemandeAppro la demande appro initialisée
     */
    private function initialisationDemandeApproReappro(): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $demandeAppro
            ->setDaTypeId(DemandeAppro::TYPE_DA_REAPPRO)
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setAgenceServiceEmetteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }
}
