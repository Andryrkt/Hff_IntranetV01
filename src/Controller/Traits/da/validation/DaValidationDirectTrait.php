<?php

namespace App\Controller\Traits\da\validation;

use DateTime;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumisAValidation;
use App\Service\autres\VersionService;
use App\Service\genererPdf\GenererPdfDaDirect;

trait DaValidationDirectTrait
{
    use DaValidationTrait;
    private GenererPdfDaDirect $genererPdfDaDirect;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationDirectTrait(): void
    {
        $this->initDaTrait();
        $this->genererPdfDaDirect = new GenererPdfDaDirect();
    }
    //==================================================================================================

    /** 
     * Création du fichier Excel et PDF pour une DA directe
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function exporterDaDirectEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf(
            $numDa,
            $numeroVersion,
            function ($numDa) {
                $this->creationPDFDirect($numDa); // Création du PDF
            }
        );
    }

    /** 
     * Création du PDF pour une DA directe
     * 
     * @param string $numDa
     * @return void
     */
    private function creationPDFDirect(string $numDa): void
    {
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $this->genererPdfDaDirect->genererPdfBonAchatValide($da, $this->getUserMail());
    }

    /**
     * Ajoute les données d'une Demande d'Achat direct dans la table `DaSoumisAValidation`
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande d'achat direct à traiter
     */
    private function ajouterDansDaSoumisAValidation(DemandeAppro $demandeAppro): void
    {
        $daSoumisAValidation = new DaSoumisAValidation();

        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daSoumisAValidationRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        $daSoumisAValidation
            ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
            ->setNumeroVersion($numeroVersion)
            ->setStatut($demandeAppro->getStatutDal())
            ->setDateSoumission(new DateTime())
            ->setUtilisateur($demandeAppro->getDemandeur())
        ;

        $this->getEntityManager()->persist($daSoumisAValidation);
        $this->getEntityManager()->flush();
    }

    /** 
     * Fonction pour mettre la DA à valider dans DW
     * 
     * @param DemandeAppro $demandeAppro la demande appro pour laquelle on génère le PDF
     */
    private function copyToDW(DemandeAppro $demandeAppro)
    {
        $this->genererPdfDaDirect->copyToDWDaAValider($demandeAppro->getNumeroDemandeAppro());
    }
}
