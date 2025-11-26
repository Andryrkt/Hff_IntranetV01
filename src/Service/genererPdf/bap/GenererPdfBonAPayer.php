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
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 20);
        $pdf->Cell(0, 6, 'BAP APPRO', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(17, true);
    }

    private function renderInfoBCAndInfoValidationBC(TCPDF $pdf, array $infoBC, array $infoValidationBC)
    {
        $w100 = $this->getUsableWidth($pdf);
        $w50  = $w100 / 2;
        $pdf->setFont('helvetica', 'B', 9);
        $pdf->Cell($w50, 5, 'INFORMATION DU BC', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell($w50, 5, 'INFORMATION VALIDATION BC', 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(3);

        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Fournisseur', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["nom_fournisseur"], 0, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Nom Validateur', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoValidationBC["validateur"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'N° FRN', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["num_fournisseur"], 0, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Date Validation', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoValidationBC["dateValidation"]->format("d/m/Y"), 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(3);

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Téléphone', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["tel_fournisseur"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Adresse FRN', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": ", 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(15, 5, '', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w100 - 15, 5, $infoBC["adr1_fournisseur"], 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(15, 5, '', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w100 - 15, 5, $infoBC["adr2_fournisseur"], 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(15, 5, '', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w100 - 15, 5, $infoBC["ptt_fournisseur"] . " " . $infoBC["adr4_fournisseur"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'N°', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["num_cde"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Date', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . date("d/m/Y", strtotime($infoBC["date_cde"])), 0, 1, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(3);

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Succursale', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["succ_cde"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Service', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["serv_cde"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Opérateur', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["nom_ope"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'N° cmd externe', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["num_cde_ext"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Référence', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["libelle_cde"], 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Montant HT', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . number_format((float) $infoBC["mtn_cde"], 2, ',', ' '), 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Montant TTC', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . number_format((float) $infoBC["ttc_cde"], 2, ',', ' '), 0, 1, '', false, '', 0, false, 'T', 'M');

        $pdf->cell(6, 5, ' -', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 5, 'Nature de l’achat', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell($w50 - 36, 5, ": " . $infoBC["type_cde"], 0, 1, '', false, '', 0, false, 'T', 'M');
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
