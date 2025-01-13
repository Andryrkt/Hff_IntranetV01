<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DitDevisSoumisAValidation;
use TCPDF;

class GenererPdfDevisSoumisAValidataion extends GeneratePdf
{
    use FormatageTrait;
    
   

    function GenererPdfDevisVente(DitDevisSoumisAValidation $devisSoumis, array $montantPdf, array $quelqueaffichage, string $email)
    {
        $pdf = new TCPDF();

        $pdf->AddPage();

        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation DEVIS', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $detailsBloc = [
            'Date soumission' => $devisSoumis->getDateHeureSoumission()->format('d/m/Y'),
            'Numéro DIT' => $devisSoumis->getNumeroDit(),
            'Numéro DEVIS' => $devisSoumis->getNumeroDevis(),
            'Version à valider' => $devisSoumis->getNumeroVersion(),
            'Sortie magasin' => $quelqueaffichage['sortieMagasin'] ?? 'N/A',
            'Achat locaux' => $quelqueaffichage['achatLocaux'] ?? 'N/A',
        ];
        
        $this->addDetailsBlock($pdf, $detailsBloc);
        

        // ================================================================================================
        $headerConfig1 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'libelleItv', 'label' => 'Libellé ITV', 'width' => 200, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'nbLigAv', 'label' => 'Nb Lig av', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'nbLigAp', 'label' => 'Nb Lig ap', 'width' => 50, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotalAv', 'label' => 'Mtt Total av', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttTotalAp', 'label' => 'Mtt Total ap', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'statut', 'label' => 'Statut', 'width' => 40, 'style' => 'font-weight: bold; text-align: center;'],
        ];
        
        $generator = new PdfTableGenerator();
        $html1 = $generator->generateTable($headerConfig1, $montantPdf['avantApres'], $montantPdf['totalAvantApres']);
        $pdf->writeHTML($html1, true, false, true, false, '');
        

            //$pdf->Ln(10, true);
//===========================================================================================
            //Titre: Controle à faire
        $this->addTitle($pdf, 'Contrôle à faire (par rapport dernière version) :');

        $details = [
            'Nouvelle intervention' => $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'],
            'Intervention supprimée' => $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'],
            'Nombre ligne modifiée' => $montantPdf['nombreStatutNouvEtSupp']['nbrModif'],
            'Montant total modifié' => $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']),
        ];
        
        $this->addSummaryDetails($pdf, $details);

//==========================================================================================================
 //Titre: Récapitulation de l'OR
 $this->addTitle($pdf, 'Récapitulation de l\'OR');
        
        $pdf->setFont('helvetica', '', 12);
        $headerConfig2 = [
            ['key' => 'itv', 'label' => 'ITV', 'width' => 40, 'style' => 'font-weight: bold;'],
            ['key' => 'mttTotal', 'label' => 'Mtt Total', 'width' => 70, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttPieces', 'label' => 'Mtt Pièces', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttMo', 'label' => 'Mtt MO', 'width' => 60, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttSt', 'label' => 'Mtt ST', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttLub', 'label' => 'Mtt LUB', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'mttAutres', 'label' => 'Mtt Autres', 'width' => 80, 'style' => 'font-weight: bold; text-align: right;'],
        ];
        
        
        $html2 = $generator->generateTable($headerConfig2, $montantPdf['recapOr'], $montantPdf['totalRecapOr']);
        $pdf->writeHTML($html2, true, false, true, false, '');
        

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY(118, 2);
            $pdf->Cell(35, 6, $email, 0, 0, 'L');


            $Dossier = $_SERVER['DOCUMENT_ROOT'] . 'Upload/dit/dev/';
            $filePath = $Dossier . 'devis_ctrl_' . $devisSoumis->getNumeroDevis() . '_' . $devisSoumis->getNumeroVersion() . '.pdf';
            $pdf->Output($filePath, 'F');
    }
    
    private function addTitle($pdf, $title, $font = 'helvetica', $style = 'B', $size = 12, $align = 'L', $lineBreak = 10)
    {
        $pdf->setFont($font, $style, $size);
        $pdf->Cell(0, 6, $title, 0, 0, $align, false, '', 0, false, 'T', 'M');
        $pdf->Ln($lineBreak, true);
    }

    private function addSummaryDetails($pdf, array $details, $font = 'helvetica', $fontSize = 10, $labelWidth = 45, $valueWidth = 50, $lineHeight = 5, $spacingAfter = 10)
    {
        $pdf->setFont($font, '', $fontSize);

        foreach ($details as $label => $value) {
            $pdf->Cell($labelWidth, 6, ' - ' . $label, 0, 0, 'L', false, '', 0, false, 'T', 'M');
            $pdf->Cell($valueWidth, 5, ': '.$value, 0, 0, '', false, '', 0, false, 'T', 'M');
            $pdf->Ln($lineHeight, true);
        }

        $pdf->Ln($spacingAfter, true);
    }

    private function addDetailsBlock($pdf, array $details, $font = 'helvetica', $labelWidth = 45, $valueWidth = 50, $lineHeight = 6, $spacing = 2)
    {
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        foreach ($details as $label => $value) {
            // Positionnement du label
            $pdf->SetXY($startX, $pdf->GetY() + $spacing);
            $pdf->setFont($font, 'B', 10);
            $pdf->Cell($labelWidth, $lineHeight, $label, 0, 0, 'L', false, '', 0, false, 'T', 'M');

            // Positionnement de la valeur
            $pdf->setFont($font, '', 10);
            $pdf->Cell($valueWidth, $lineHeight, ': ' . $value, 0, 1, '', false, '', 0, false, 'T', 'M');
        }

        // Ajout d'un espace après le bloc
        $pdf->Ln(10, true);
    }


}