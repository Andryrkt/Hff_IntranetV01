<?php

namespace App\Controller\Traits\da;

use App\Controller\Controller;
use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;

trait DaNewAvecDitTrait
{
    use EntityManagerAwareTrait;

    /** 
     * Initialisation des valeurs par défaut pour une Demande d'Achat avec DIT
     * 
     * @param DemandeAppro $demandeAppro Objet de la demande d'achat à initialiser
     * @param DemandeIntervention $dit DIT associé à la demande d'achat
     */
    private function initialisationDemandeAppro(DemandeAppro $demandeAppro, DemandeIntervention $dit)
    {
        $demandeAppro
            ->setDit($dit)
            ->setObjetDal($dit->getObjetDemande())
            ->setDetailDal($dit->getDetailDemande())
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
            ->setUser(Controller::getUser())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;
    }
}
