<?php

namespace App\Controller\Traits\da\validation;

use App\Entity\da\DemandeAppro;
use App\Service\da\EmailDaService;
use App\Service\genererPdf\GenererPdfDaDirect;

trait DaValidationDirectTrait
{
    use DaValidationTrait;

    //==================================================================================================
    private EmailDaService $emailDaService;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationDirectTrait(): void
    {
        $this->initDaTrait();
        $this->emailDaService = new EmailDaService;
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
        $genererPdfDaDirect = new GenererPdfDaDirect;
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $genererPdfDaDirect->genererPdfBonAchatValide($da, $this->getUserMail());
    }

    /** 
     * Méthode pour envoyer une email de validation à l'Atelier et l'Appro
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    private function envoyerMailValidationDaDirect(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->emailDaService->envoyerMailValidationDaAvecDitAuxAtelier($demandeAppro, $resultatExport, $tab); // envoi de mail à l'atelier
        $this->emailDaService->envoyerMailValidationDaAvecDitAuxAppro($demandeAppro, $resultatExport, $tab); // envoi de mail à l'appro
    }
}
