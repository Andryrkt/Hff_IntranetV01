<?php

namespace App\Service\genererPdf\bap;

use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GeneratePdf;

class GenererPdfBonAPayer extends GeneratePdf
{
    /**
     * Fonction pour générer le PDF du bon à payer
     */
    public function genererPageDeGarde(array $infoBC, array $infoValidationBC, array $infoMateriel, array $dataRecapOR, DemandeAppro $demandeAppro, array $infoFacBl): string
    {
        $pdf = new TCPDF();
        $pdf->AddPage();

        $numDa = $demandeAppro->getNumeroDemandeAppro();
        

        // Sauvegarder le PDF
        return $this->savePDF($pdf, $numDa, "I");
    }

    private function savePDF(TCPDF $pdf, string $numDa, string $dest = "F"): string
    {
        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $fileName = "$Dossier/BAP_{$numDa}_" . date("Y-m-d_H-i-s") . ".pdf";
        $pdf->Output($fileName, $dest);
        return $fileName;
    }
}
