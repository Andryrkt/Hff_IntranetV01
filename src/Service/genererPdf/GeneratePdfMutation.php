<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdfMutation extends GeneratePdf
{
    /**
     * Genere le PDF DEMANDE DE MUTATION (MUT)
     */
    public function genererPDF($tab)
    {
        $pdf = new TCPDF();

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right'];
        $w50 = $usable_width / 2;

        $pdf->AddPage();

        // tête de page 
        $pdf->setY(2);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->setX($w_total - ($pdf->GetStringWidth($tab['MailUser']) + $pdf->GetStringWidth('Email émetteur : ') + 7));
        $pdf->Cell($pdf->GetStringWidth('Email émetteur : '), 8, 'Email émetteur : ', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($pdf->GetStringWidth($tab['MailUser']), 8, $tab['MailUser'], 0, 1, 'R');

        $pdf->Line(0, 10, $w_total, 10);

        // Logo HFF
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 5, 10, 40, 0, 'jpg');

        // Grand titre du pdf
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->setXY(73, 11);
        $pdf->Cell(0, 10, 'MUTATION', 0, 0);
        $pdf->setXY(126, 11);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 10, 'Le: ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->setXY(126 + $pdf->GetStringWidth('Le :'), 11);
        $pdf->Cell(0, 10, $tab['dateS'], 0, 0);
        $pdf->setXY(-33, 11);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 10, $tab['NumMut'], 0, 1);

        $pdf->Ln(3); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 9, 'Nom : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 20, 9, $tab['Nom'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 9, 'Prénom : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 75, 9, $tab['Prenoms'], 1, 0);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(25, 9, 'Matricule :', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(30, 9, $tab['matr'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Catégorie professionnelle : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['CategoriePers'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Agence d’origine :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['agenceOrigine'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Service d’origine : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['serviceOrigine'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(37, 9, 'Date d’affectation :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(21, 9, $tab['dateAffectation'], 1, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(38, 9, 'Lieu d’affectation :', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 96, 9, $tab['lieuAffectation'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 9, 'Motif : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 20, 9, $tab['motif'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Agence de destination :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['agenceDestination'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Service de destination :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['serviceDestination'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 9, 'Client : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 20, 9, $tab['client'], 1, 1);

        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'INDEMNITE D\'INSTALLATION:', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(45, 9, $tab['avanceSurIndemnite'], 1, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(10, 9, '', 0, 0, 'C');
        $pdf->Cell($usable_width - 158, 9, 'Durée en jour :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(45, 9, $tab['NbJ'], 1, 1, 'C');

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('INDEMNITE D\'INSTALLATION:') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(2); // Nouvelle ligne
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Indemnité forfaitaire :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(45, 9, $tab['indemnite'], 1, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(10, 9, '', 0, 0, 'C');
        $pdf->Cell($usable_width - 158, 9, 'Supplément :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(45, 9, $tab['supplement'], 1, 1, 'C');


        $pdf->Ln(2); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'Total d’indemnités à verser :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['totalIndemnite'], 1, 1);

        $pdf->Ln(4); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(20, 10, 'Autres: ', 0, 0);

        $w_tab = $usable_width - 20;

        $pdf->setXY(30, $pdf->GetY() + 1);
        $pdf->Cell($w_tab - $w50 + 39, 10,  'MOTIF', 1, 0, 'C');
        $pdf->Cell($w50 - 39, 10, 'MONTANT', 1, 1, 'C');

        $pdf->setX(30);
        $pdf->Cell($w_tab - $w50 + 39, 10,  $tab['motifdep01'], 1, 0, 'L');
        $pdf->Cell($w50 - 39, 10, $tab['montdep01'] . '  ', 1, 1, 'R');

        $pdf->setX(30);
        $pdf->Cell($w_tab - $w50 + 39, 10,  $tab['motifdep02'], 1, 0, 'L');
        $pdf->Cell($w50 - 39, 10, $tab['montdep02'] . '  ', 1, 1, 'R');

        $pdf->setX(30);
        $pdf->Cell($w_tab - $w50 + 39, 10,  'Total autres dépenses :', 1, 0, 'C');
        $pdf->Cell($w50 - 39, 10, $tab['totaldep'] . '  ', 1, 1, 'R');

        $pdf->Ln(4); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(58, 9, 'MONTANT TOTAL A PAYER :', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($usable_width - 58, 9, $tab['totalGeneral'], 1, 1);

        $pdf->Ln(4); // Nouvelle ligne

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(39, 10, 'Mode de paiement : ', 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($w50 - 39, 10, $tab['libModPaie'], 1, 0, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(39, 10, $tab['mode'] . ' : ', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell($w50 - 39, 10, $tab['valModPaie'], 1, 1, 'C');

        $pdf->setTextColor(255, 0, 0); // rouge
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(8, 7, 'NB : ', 0, 0);
        $pdf->setFont('helvetica', '', 10);
        $pdf->Cell(10, 7, $tab['message'], 0, 1);

        // Ligne de séparation
        $pdf->Line(0, $pdf->GetY(), $w_total, $pdf->GetY());

        // génération de fichier
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . '/mut/';
        $pdf->Output($Dossier . $tab['NumMut'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
    }
}
