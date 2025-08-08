<?php

namespace App\Service\genererPdf;

use App\Entity\da\DemandeAppro;
use TCPDF;

class GenererPdfDaDirect extends GeneratePdf
{
    /** 
     * Fonction pour générer le PDF à valider DW d'une DA sans DIT
     * 
     * @param DemandeAppro $da la DA correspondante
     * @param iterable $dals les DALs associés à la DA
     * @param string $userMail l'email de l'utilisateur (optionnel)
     * 
     * @return void
     */
    public function genererPdfAValiderDW(DemandeAppro $da, iterable $dals, string $userMail = ''): void
    {
        $pdf = new TCPDF();
        $numDa = $da->getNumeroDemandeAppro();
        $generator = new PdfTableGenerator();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'DEMANDE D\'ACHAT', 0, 0, 'C', false, '', 0, false, 'T', 'M');


        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $numDa, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $da->getDateCreation()->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        //========================================================================================
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Objet :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(0, 6, $da->getObjetDal(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Détails :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->MultiCell(164, 50, $da->getDetailDal(), 1, '', 0, 0, '', '', true);
        //$pdf->cell(165, 10, , 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(3, true);
        $pdf->setAbsY(83);

        //===================================================================================================
        /**AGENCE-SERVICE */
        $this->renderTextWithLine($pdf, 'Agence - Service');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Emetteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $da->getAgenceServiceEmetteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Débiteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $da->getAgenceServiceDebiteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /** ARTICLE VALIDES */
        $this->renderTextWithLine($pdf, 'Articles demandés');

        $pdf->Ln(3);

        $pdf->SetTextColor(0, 0, 0);
        $header = [
            ['key' => 'designation', 'label' => 'Désignation', 'width' => 300, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'qte',         'label' => 'Qté',         'width' => 60,  'style' => 'font-weight: bold; text-align: center;'],
        ];
        $html1 = $generator->generateTableForDaAValiderDW($header, $dals);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //=========================================================================================
        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetY(2);
        $pdf->writeHTMLCell(0, 6, '', '', "email : $userMail", 0, 1, false, true, 'R');

        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa/A valider";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $pdf->Output("$Dossier/$numDa.pdf", 'F');
    }

    /** 
     * Fonction pour générer le PDF d'un bon d'achat validé d'une DA sans DIT
     * 
     * @param DemandeAppro $da la DA correspondante
     * @param string $userMail l'email de l'utilisateur (optionnel)
     * 
     * @return void
     */
    public function genererPdfBonAchatValide(DemandeAppro $da, string $userMail = ''): void
    {
        $pdf = new TCPDF();
        $dals = $da->getDAL();
        $numDa = $da->getNumeroDemandeAppro();
        $generator = new PdfTableGenerator();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'DEMANDE D\'ACHAT', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $numDa, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $da->getDateCreation()->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        //========================================================================================
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Objet :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(0, 6, $da->getObjetDal(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Détails :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->MultiCell(164, 50, $da->getDetailDal(), 1, '', 0, 0, '', '', true);
        //$pdf->cell(165, 10, , 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(3, true);
        $pdf->setAbsY(83);

        //===================================================================================================
        /**AGENCE-SERVICE */
        $this->renderTextWithLine($pdf, 'Agence - Service');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Emetteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $da->getAgenceServiceEmetteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Débiteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $da->getAgenceServiceDebiteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /** ARTICLE VALIDES */
        $this->renderTextWithLine($pdf, 'Articles validés');

        $pdf->Ln(3);

        $pdf->SetTextColor(0, 0, 0);
        $header = [
            ['key' => 'reference',   'label' => 'Référence',   'width' => 110, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'designation', 'label' => 'Désignation', 'width' => 190, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'pu1',         'label' => 'PU',          'width' => 80,  'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'qte',         'label' => 'Qté',         'width' => 60,  'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'mttTotal',    'label' => 'Montant',     'width' => 100, 'style' => 'font-weight: bold; text-align: right;'],
        ];
        $html1 = $generator->generateTableForDA($header, $dals);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //=========================================================================================
        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetY(2);
        $pdf->writeHTMLCell(0, 6, '', '', "email : $userMail", 0, 1, false, true, 'R');

        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $pdf->Output("$Dossier/$numDa.pdf", 'F');
    }

    private function renderTextWithLine($pdf, $text, $totalWidth = 190, $lineOffset = 3, $font = 'helvetica', $fontStyle = 'B', $fontSize = 11, $textColor = [14, 65, 148], $lineColor = [14, 65, 148], $lineHeight = 1)
    {
        // Set font and text color
        $pdf->setFont($font, $fontStyle, $fontSize);
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

        // Calculate text width
        $textWidth = $pdf->GetStringWidth($text);

        // Add the text
        $pdf->Cell($textWidth, 6, $text, 0, 0, 'L');

        // Set fill color for the line
        $pdf->SetFillColor($lineColor[0], $lineColor[1], $lineColor[2]);

        // Calculate the remaining width for the line
        $remainingWidth = $totalWidth - $textWidth - $lineOffset;

        // Calculate the position for the line (next to the text)
        $lineStartX = $pdf->GetX() + $lineOffset; // Add a small offset
        $lineStartY = $pdf->GetY() + 3; // Adjust for alignment

        // Draw the line
        if ($remainingWidth > 0) { // Only draw if there is space left for the line
            $pdf->Rect($lineStartX, $lineStartY, $remainingWidth, $lineHeight, 'F');
        }

        // Move to the next line
        $pdf->Ln(6, true);
    }
}
