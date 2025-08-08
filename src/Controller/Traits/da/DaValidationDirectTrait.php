<?php

namespace App\Controller\Traits\da;

trait DaValidationDirectTrait
{
    use DaTrait;
    use DaValidationTrait;

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
            function ($numDa, $donnees) {
                $this->creationPDFDirect($numDa, $donnees); // Création du PDF
            }
        );
    }

    /** 
     * Création du PDF pour une DA directe
     * 
     * @param string $numDa
     * @param array $donnees
     * @return void
     */
    private function creationPDFDirect(string $numDa, array $donnees): void {}
}
