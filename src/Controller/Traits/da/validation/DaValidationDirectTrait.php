<?php

namespace App\Controller\Traits\da\validation;

use App\Service\genererPdf\GenererPdfDaDirect;

trait DaValidationDirectTrait
{
    use DaValidationTrait;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationDirectTrait(): void
    {
        $this->initDaTrait();
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
}
