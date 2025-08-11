<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DaSoumisAValidation;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GenererPdfDaDirect;
use DateTime;

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
            ->setStatutDal(DemandeAppro::STATUT_A_VALIDE_DW)
            ->setUser($this->getUser())
            ->setNumeroDemandeAppro($this->autoDecrement('DAP'))
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaiteAutomatique() // Définit la date de fin souhaitée automatiquement à 3 jours après la date actuelle
        ;

        return $demandeAppro;
    }

    /**
     * Ajoute les données d'une Demande d'Achat direct dans la table `DaSoumisAValidation`
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande d'achat direct à traiter
     */
    private function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion(1)
            ->setStatut($demandeAppro->getStatutDal())
            ->setDateSoumission(new DateTime())
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
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
