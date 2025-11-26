<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GeneratePdf;

class GenererPdfBonAPayer extends GeneratePdf
{
    use FormatageTrait;

    /**
     * Fonction pour générer le PDF du bon à payer
     */
    public function genererPageDeGarde(array $infoBC, array $infoValidationBC, array $infoMateriel, array $dataRecapOR, DemandeAppro $demandeAppro, array $infoFacBl): string
    {
        $pdf = new TCPDF();

        $this->renderHeader($pdf);

        $w100 = $this->getUsableWidth($pdf);

        $this->renderInfoBCAndInfoValidationBC($pdf, $w100, $infoBC, $infoValidationBC);
        $this->renderInfoMateriel($pdf, $w100, $infoMateriel);
        // $this->renderDataRecapOR($pdf, $dataRecapOR);
        // $this->renderRecapDA($pdf, $demandeAppro);
        // $this->renderInfoFacBl($pdf, $infoFacBl);

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

    private function renderInfoBCAndInfoValidationBC(TCPDF $pdf, $w100, array $infoBC, array $infoValidationBC)
    {
        $w50  = $w100 / 2;
        $pdf->setFont('helvetica', 'B', 9);
        $this->cell($pdf, $w50, 5, 'INFORMATION DU BC', 0);
        $this->cell($pdf, $w50, 5, 'INFORMATION VALIDATION BC', 1);
        $pdf->Ln(3);

        $pdf->setFont('helvetica', '', 9);

        // Infos principales
        $this->printInfo($pdf, 'Fournisseur', $infoBC["nom_fournisseur"], $w50, 0);
        $this->printInfo($pdf, 'Nom Validateur', $infoValidationBC["validateur"] ?? "-", $w50);

        $this->printInfo($pdf, 'N° FRN', $infoBC["num_fournisseur"], $w50, 0);
        $this->printInfo($pdf, 'Date Validation', $infoValidationBC["dateValidation"] ? $infoValidationBC["dateValidation"]->format("d/m/Y") : "-", $w50);
        $pdf->Ln(3);

        $this->printInfo($pdf, 'Téléphone', $infoBC["tel_fournisseur"], $w50);

        // Adresse
        $this->printAdresse($pdf, $infoBC, $w100);

        $this->printInfo($pdf, 'N°', $infoBC["num_cde"] ?? "-", $w50);
        $this->printInfo($pdf, 'Date', $infoBC["date_cde"] ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-", $w50);
        $pdf->Ln(3);

        $this->printInfo($pdf, 'Succursale', $infoBC["succ_cde"] ?? "-", $w50);
        $this->printInfo($pdf, 'Service', $infoBC["serv_cde"] ?? "-", $w50);
        $this->printInfo($pdf, 'Opérateur', $infoBC["nom_ope"] ?? "-", $w50);
        $this->printInfo($pdf, 'N° cmd externe', $infoBC["num_cde_ext"] ?? "-", $w50);
        $this->printInfo($pdf, 'Référence', $infoBC["libelle_cde"] ?? "-", $w50);
        $this->printInfo($pdf, 'Montant HT', $this->formaterPrix($infoBC["mtn_cde"]), $w50);
        $this->printInfo($pdf, 'Montant TTC', $this->formaterPrix($infoBC["ttc_cde"]), $w50);
        $this->printInfo($pdf, 'Nature de l’achat', $infoBC["type_cde"] ?? "-", $w50);
    }

    private function renderInfoMateriel(TCPDF $pdf, $w100, array $infoMateriel) {}

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

    private function cell(TCPDF $pdf, $w, $h, $txt, $ln)
    {
        $pdf->Cell($w, $h, $txt, 0, $ln, 'L', false, '', 0, false, 'T', 'M');
    }

    private function printInfo(TCPDF $pdf, $label, $value, $w50, $endLine = 1)
    {
        $this->cell($pdf, 6, 5, ' -', 0);
        $this->cell($pdf, 30, 5, $label, 0);
        $this->cell($pdf, $w50 - 36, 5, ": " . $value, $endLine);
    }

    private function printAdresse(TCPDF $pdf, $infoBC, $w100)
    {
        $this->cell($pdf, 6, 5, ' -', 0);
        $this->cell($pdf, 30, 5, 'Adresse FRN', 0);
        $this->cell($pdf, $w100 - 36, 5, ": ", 1);

        // Adresses multi-lignes
        $this->cell($pdf, 15, 5, '', 0);
        $this->cell($pdf, $w100 - 15, 5, $infoBC["adr1_fournisseur"], 1);

        $this->cell($pdf, 15, 5, '', 0);
        $this->cell($pdf, $w100 - 15, 5, $infoBC["adr2_fournisseur"], 1);

        $this->cell($pdf, 15, 5, '', 0);
        $this->cell($pdf, $w100 - 15, 5, $infoBC["ptt_fournisseur"] . " " . $infoBC["adr4_fournisseur"], 1);
    }
}
