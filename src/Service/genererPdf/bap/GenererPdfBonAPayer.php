<?php

namespace App\Service\genererPdf\bap;

use App\Controller\Traits\FormatageTrait;
use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGeneratorFlexible;

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
        $this->renderDataRecapOR($pdf, $dataRecapOR);
        $this->renderRecapDA($pdf, $w100, $demandeAppro);
        $this->renderInfoFacBl($pdf, $w100, $infoFacBl);

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
        $this->addInfoLine($pdf, 'Fournisseur', $infoBC["nom_fournisseur"], $w100, 30, 0, 0);
        $this->addInfoLine($pdf, 'Nom Validateur', $infoValidationBC["validateur"] ?? "-", $w100, 30, 0);

        $this->addInfoLine($pdf, 'N° FRN', $infoBC["num_fournisseur"], $w100, 30, 0, 0);
        $this->addInfoLine($pdf, 'Date Validation', $infoValidationBC["dateValidation"] ? $infoValidationBC["dateValidation"]->format("d/m/Y") : "-", $w100, 30, 0);
        $pdf->Ln(3);

        $this->addInfoLine($pdf, 'Téléphone', $infoBC["tel_fournisseur"], $w100, 30, 0);

        // Adresse
        $this->printAdresse($pdf, $infoBC, $w100);

        $this->addInfoLine($pdf, 'N°', $infoBC["num_cde"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'Date', $infoBC["date_cde"] ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-", $w100, 30, 0);
        $pdf->Ln(3);

        $this->addInfoLine($pdf, 'Succursale', $infoBC["succ_cde"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'Service', $infoBC["serv_cde"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'Opérateur', $infoBC["nom_ope"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'N° cmd externe', $infoBC["num_cde_ext"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'Référence', $infoBC["libelle_cde"] ?? "-", $w100, 30, 0);
        $this->addInfoLine($pdf, 'Montant HT', $this->formaterPrix($infoBC["mtn_cde"], '.'), $w100, 30, 0);
        $this->addInfoLine($pdf, 'Montant TTC', $this->formaterPrix($infoBC["ttc_cde"], '.'), $w100, 30, 0);
        $this->addInfoLine($pdf, 'Nature de l’achat', $infoBC["type_cde"] ?? "-", $w100, 30, 0);
    }

    private function renderInfoMateriel(TCPDF $pdf, $w100, array $infoMateriel)
    {
        $pdf->Ln(3);
        $pdf->setFont('helvetica', 'B', 9);
        $this->cell($pdf, $w100, 5, 'LA COMMANDE CONCERNE LE MATERIEL SUIVANT :', 1);
        $pdf->Ln(3);

        $pdf->setFont('helvetica', '', 9);
        $this->addInfoLine($pdf, '', $infoMateriel["designation"] ?? "-", $w100, '', 6);
        $this->addInfoLine($pdf, 'N° série', $infoMateriel["numserie"] ?? "-", $w100, 13, 6);
        $this->addInfoLine($pdf, 'Identité', $infoMateriel["identite"] ?? "-", $w100, 13, 6);
    }

    private function renderDataRecapOR(TCPDF $pdf, array $dataRecapOR)
    {
        $pdf->Ln(3);
        $pdf->setFont('helvetica', 'B', 9);
        $this->cell($pdf, 0, 5, 'RECAPITULATION DE L’OR', 1);
        $pdf->Ln(3);

        $tableGenerator = new PdfTableGeneratorFlexible();
        $tableGenerator->setOptions([
            'table_attributes' => 'border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;"',
            'header_row_style' => 'background-color: #D3D3D3;',
            'footer_row_style' => 'background-color: #D3D3D3;'
        ]);

        $pdf->setFont('helvetica', '', 9);
        $html = $tableGenerator->generateTable($dataRecapOR["header"], $dataRecapOR["body"], $dataRecapOR["footer"]);
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function renderRecapDA(TCPDF $pdf, $w100, DemandeAppro $demandeAppro)
    {
        $pdf->setFont('helvetica', 'B', 9);
        $this->cell($pdf, 0, 5, 'RECAPITULATION DE LA DA', 1);
        $pdf->ln(3);

        $pdf->setFont('helvetica', '', 9);

        $this->addInfoLine($pdf, 'N° DA', $demandeAppro->getNumeroDemandeAppro(), $w100, 25, 6);
        $this->addInfoLine($pdf, 'Date de création', $demandeAppro->getDateCreation()->format('d/m/Y'), $w100, 25, 6);
        $this->addInfoLine($pdf, 'Objet', $demandeAppro->getObjetDal(), $w100, 25, 6);
        $this->addInfoLine($pdf, 'Agence – service émetteur', $demandeAppro->getAgenceServiceEmetteur(), $w100, 39, 6);
        $this->addInfoLine($pdf, 'Agence et service débiteur', $demandeAppro->getAgenceServiceDebiteur(), $w100, 39, 6);
    }

    private function renderInfoFacBl(TCPDF $pdf, $w100, array $infoFacBl)
    {
        $pdf->ln(3);
        $pdf->setFont('helvetica', 'B', 9);
        $this->cell($pdf, 0, 5, 'INFO BL / FAC FOURNISSEUR', 1);
        $pdf->ln(3);

        $pdf->setFont('helvetica', '', 9);

        $this->addInfoLine($pdf, 'Réf', $infoFacBl["refBlFac"] ?? "-", $w100 / 2, 8, 6, 0);
        $this->addInfoLine($pdf, 'N° livraison IPS', $infoFacBl["numLivIPS"] ?? "-", $w100 / 2, 27, 6);
        $this->addInfoLine($pdf, 'Date', $infoFacBl["dateBlFac"] ? $infoFacBl["dateBlFac"]->format('d/m/Y') : "-", $w100 / 2, 8, 6, 0);
        $this->addInfoLine($pdf, 'Date livraison IPS', $infoFacBl["dateLivIPS"] ? date("d/m/Y", strtotime($infoFacBl["dateLivIPS"])) : "-", $w100 / 2, 27, 6);
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

    private function cell(TCPDF $pdf, $w, $h, $txt, $ln)
    {
        $pdf->Cell($w, $h, $txt, 0, $ln, 'L', false, '', 0, false, 'T', 'M');
    }

    private function addInfoLine(TCPDF $pdf, $label, $value, $w100, $labelWidth, $decalage = 6, $endLine = 1)
    {
        // Décalage + tiret
        if ($decalage > 0) $this->cell($pdf, $decalage, 5, '', 0);
        $this->cell($pdf, 6, 5, ' -', 0);

        $w = $decalage > 0 ? $w100 - $decalage - 6 : $w100 / 2 - 6;

        if (!empty($label)) {
            $this->cell($pdf, $labelWidth, 5, $label, 0);
            $this->cell($pdf, $w - $labelWidth, 5, ": " . $value, $endLine);
        } else {
            $this->cell($pdf, $w, 5, $value, $endLine);
        }
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
