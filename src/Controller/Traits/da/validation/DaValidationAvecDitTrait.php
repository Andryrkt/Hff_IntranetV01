<?php

namespace App\Controller\Traits\da\validation;

use App\Service\genererPdf\da\GenererPdfDaAvecDit;

trait DaValidationAvecDitTrait
{
    use DaValidationTrait;

    //====================================================================================================
    private GenererPdfDaAvecDit $genererPdfDaAvecDit;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationAvecDitTrait(): void
    {
        $this->initDaTrait();
        $this->genererPdfDaAvecDit = new GenererPdfDaAvecDit;
    }
    //====================================================================================================

    /** 
     * Création du fichier Excel et PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function exporterDaAvecDitEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf(
            $numDa,
            $numeroVersion,
            function ($numDa) {
                $this->creationPDFAvecDit($numDa); // Création du PDF
            }
        );
    }

    /** 
     * Création du PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @return void
     */
    private function creationPDFAvecDit(string $numDa): void
    {
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $this->genererPdfDaAvecDit->genererPdfBonAchatValide($da->getDit(), $da, $this->getUserMail());
    }
}
