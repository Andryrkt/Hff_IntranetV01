<?php

namespace App\Service\genererPdf;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\dit\DemandeIntervention;
use TCPDF;

class GenererPdfDa extends GeneratePdf
{
    public function genererPdf(DemandeIntervention $dit, DemandeAppro $da, $dals)
    {
        $pdf = new TCPDF();
        $generator = new PdfTableGenerator();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        //$pdf->Cell(45, 12, 'LOGO', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Cell(110, 6, 'DEMANDE D\'APPROVISIONNEMENT', 0, 0, 'C', false, '', 0, false, 'T', 'M');


        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $da->getNumeroDemandeAppro(), 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->setFont('helvetica', 'B', 12);
        $pdf->setAbsX(55);
        if ($dit->getTypeDocument() !== null) {
            $descriptionTypeDocument = $dit->getTypeDocument()->getDescription();
        } else {
            $descriptionTypeDocument = ''; // Ou toute autre valeur par défaut appropriée
        }
        $pdf->cell(110, 6, $descriptionTypeDocument, 0, 0, 'C', false, '', 0, false, 'T', 'M');

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

        $this->renderTextWithLine($pdf, 'Intervention');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Date prévue :', 0, 0, '', false, '', 0, false, 'T', 'M');
        if ($dit->getDatePrevueTravaux() !== null && !empty($dit->getDatePrevueTravaux())) {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux()->format('d/m/Y'), 1, 0, '', false, '', 0, false, 'T', 'M');
        } else {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux(), 1, 0, '', false, '', 0, false, 'T', 'M');
        }
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getIdNiveauUrgence()->getDescription(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /**AGENCE-SERVICE */

        $this->renderTextWithLine($pdf, 'Agence - Service');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Emetteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $dit->getAgenceServiceEmetteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Débiteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getAgenceServiceDebiteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //====================================================================================================
        /**REPARATION */
        $this->renderTextWithLine($pdf, 'Réparation');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Type :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getInternetExterne(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(23, 6, 'Réparation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(35, 6, $dit->getTypeReparation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(25, 6, 'Réalisé par :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getReparationRealise(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //========================================================================================================
        /** CARACTERISTIQUE MATERIEL */
        $this->renderTextWithLine($pdf, 'Caractéristiques du matériel');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->cell(25, 6, 'Désignation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(70, 6, $dit->getDesignation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(140);
        $pdf->cell(20, 6, 'N° Série :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getNumSerie(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);


        $pdf->cell(25, 6, 'N° Parc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getNumParc(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(21, 6, 'Modèle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(37, 6, $dit->getModele(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(30, 6, 'Constructeur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getConstructeur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        $pdf->cell(25, 6, 'Casier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $casier = $dit->getCasier();
        if (mb_strlen($casier) > 17) {
            $casier = mb_substr($casier, 0, 15) . '...';
        }
        $pdf->cell(40, 6, $casier, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(80);
        $pdf->cell(23, 6, 'Id Matériel :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $dit->getIdMateriel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(33, 6, 'livraison partielle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getLivraisonPartiel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /** ETAT MACHINE */
        $this->renderTextWithLine($pdf, 'Etat machine');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->MultiCell(25, 6, "Heures :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $dit->getHeure(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(135);
        $pdf->cell(25, 6, 'Kilométrage :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getKm(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /** ARTICLE VALIDES */
        $this->renderTextWithLine($pdf, 'Articles validés');

        $pdf->Ln(3);

        $pdf->SetTextColor(0, 0, 0);
        $header = [
            ['key' => 'reference', 'label' => 'Référence', 'width' => 110, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'designation', 'label' => 'Désignation', 'width' => 190, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'pu1', 'label' => 'PU', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'qte', 'label' => 'Qté', 'width' => 60, 'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'mttTotal', 'label' => 'Montant', 'width' => 100, 'style' => 'font-weight: bold; text-align: right;'],
        ];
        $html1 = $generator->generateTableForDA($header, $dals);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //=========================================================================================
        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetXY(110, 2);
        $pdf->Cell(35, 6, "email : " . Controller::getMailUser(), 0, 0, 'L');

        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $da->getNumeroDemandeAppro() . '/';

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $pdf->Output($Dossier . $da->getNumeroDemandeAppro() . '.pdf', 'F');
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
