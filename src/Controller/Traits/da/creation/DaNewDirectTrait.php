<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GenererPdfDaDirect;

trait DaNewDirectTrait
{
    use DaNewTrait;

    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewDirectTrait(): void
    {
        $this->initDaTrait();
    }
    //=====================================================================================

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
            ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }

    /** 
     * Fonction pour créer le PDF sans Dit à valider DW
     * 
     * @param DemandeAppro $demandeAppro la demande appro pour laquelle on génère le PDF
     */
    private function creationPdfSansDitAvaliderDW(DemandeAppro $demandeAppro)
    {
        $genererPdfDaDirect = new GenererPdfDaDirect;
        $dals = $demandeAppro->getDAL();

        $genererPdfDaDirect->genererPdfAValiderDW($demandeAppro, $dals, $this->getUserMail());
        $genererPdfDaDirect->copyToDWDaAValider($demandeAppro->getNumeroDemandeAppro());
    }
}
