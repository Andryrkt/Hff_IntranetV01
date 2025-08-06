<?php

namespace App\Controller\Traits\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;

trait DaNewDirectTrait
{
    /** 
     * Fonction pour initialiser une demande appro direct
     * 
     * @return DemandeAppro la demande appro initialisée
     */
    private function initialisationDemandeApproDirect(): DemandeAppro
    {
        $demandeAppro = new DemandeAppro;

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $demandeAppro
            ->setAchatDirect(true)
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setAgenceServiceEmetteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setUser(Controller::getUser())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }
}
