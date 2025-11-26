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

        $this->renderHeader($pdf);

        $this->renderInfoBCAndInfoValidationBC($pdf, $infoBC, $infoValidationBC);

        $numDa = $demandeAppro->getNumeroDemandeAppro();


        // Sauvegarder le PDF
        return $this->savePDF($pdf, $numDa, "I");
    }

    private function renderHeader(TCPDF $pdf)
    {
        $margins = $pdf->getMargins();
        $originalTop = $margins['top'];
        $originalLeft = $margins['left'];
        $originalRight = $margins['right'];

        $pdf->setMargins($originalLeft + 10, $originalTop, $originalRight, true);
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 25);
        $pdf->Cell(0, 6, 'BAP APPRO', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(20, true);
    }

    private function renderInfoBCAndInfoValidationBC(TCPDF $pdf, array $infoBC, array $infoValidationBC)
    {
        $w100 = $this->getUsableWidth($pdf);
        $w50  = $w100 / 2;
        $pdf->setFont('helvetica', 'B', 9);
        $pdf->Cell($w50, 6, 'INFORMATION DU BC', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell($w50, 6, 'INFORMATION VALIDATION BC', 1, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->Ln(3);

        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(6, 6, ' -', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(25, 6, 'Fournisseur', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 31, 6, ": " . $infoBC["nom_fournisseur"], 1, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 6, ' -', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, 'Nom Validateur', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 6, ": " . $infoValidationBC["validateur"], 1, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 6, ' -', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(25, 6, 'N° FRN', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 31, 6, ": " . $infoBC["num_fournisseur"], 1, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 6, ' -', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, 'Date Validation', 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 6, ": " . $infoValidationBC["dateValidation"]->format("d/m/Y"), 1, 0, '', false, '', 0, false, 'T', 'M');
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

    private function getUsableWidth(TCPDF $pdf)
    {
        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        return $w_total - $margins['left'] - $margins['right'];
    }
}
