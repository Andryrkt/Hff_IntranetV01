<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdfDom extends GeneratePdf
{
    /**
     * Genere le PDF DEMANDE D'ORDRE DE MISSION (DOM)
     */
    public function genererPDF(array $tab)
    {
        $pdf = new TCPDF();

        $w50 = $this->getHalfWidth($pdf);

        $pdf->AddPage();

        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 10, 10, 30, '', 'jpg');
        // tête de page 
        $pdf->setY(0);
        $pdf->SetFont('pdfatimesbi', '', 8);
        $pdf->Cell(0, 8, $tab['MailUser'], 0, 1, 'R');

        // Logo HFF
        $logoPath = __DIR__ . '/image/logoHFF.jpg';
        $pdf->Image($logoPath, 10, 10, 30, '', 'jpg');

        // Grand titre du pdf
        $pdf->SetFont('pdfatimesbi', 'B', 16);
        $pdf->Cell(0, 10, 'ORDRE DE MISSION ', 0, 1, 'C');
        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(100, 10, 'Agence/Service débiteur : ' . $tab['codeServiceDebitteur'] . '-' . $tab['serviceDebitteur'], 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setX(120);
        $pdf->Cell(30, 10, 'Le: ' . $tab['dateS'], 0, 0);
        $pdf->Cell(0, 10, $tab['NumDom'], 0, 1, 'R');

        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->Cell($w50, 10, 'Type  : ' . $tab['typMiss'], 1, 0);

        $pdf->SetFont('pdfatimesbi', '', 10);
        $pdf->Cell($w50, 10, 'Catégorie : ' . $tab['CategoriePers'], 1, 1);

        $pdf->SetFont('pdfatimesbi', '', 12);
        $pdf->Cell($w50, 10, 'Agence: ' . $tab['Code_serv'], 1, 0);
        $pdf->Cell($w50, 10, 'Service: ' . $tab['serv'], 1, 1);

        $pdf->Cell(0, 10, 'Nom : ' . $tab['Nom'], 1, 1);
        $pdf->Cell($w50, 10, 'Prénoms: ' . $tab['Prenoms'], 1, 0);
        $pdf->Cell($w50, 10, 'Matricule : ' . $tab['matr'], 1, 1);
        $pdf->Cell(0, 10, 'Motif : ' . $tab['motif'], 1, 1);

        $pdf->Cell(0, 10, 'Période: ' . $tab['NbJ'] . ' Jour(s)    Soit du ' . $tab['dateD'] . ' à  ' . $tab['heureD'] . ' Heures ' . ' au  ' . $tab['dateF'] . '  à ' . $tab['heureF'] . ' Heures ', 1, 1);

        $pdf->Cell($w50, 10, 'Site : ' . $tab['Site'], 1, 0);
        $pdf->SetFont('pdfatimesbi', '', 10);
        $pdf->Cell($w50, 10, 'Lieu d intervention : ' . $tab['lieu'], 1, 1);

        $pdf->SetFont('pdfatimesbi', '', 12);

        $pdf->Cell($w50, 10, 'Client : ' . $tab['Client'], 1, 0);
        $pdf->Cell($w50, 10, 'N° fiche : ' . $tab['fiche'], 1, 1);

        $pdf->Cell($w50, 10, 'Véhicule société : ' . $tab['vehicule'], 1, 0);
        $pdf->Cell($w50, 10, 'N° de véhicule: ' . $tab['numvehicul'], 1, 1);

        $pdf->Cell($w50, 10, 'Indemnité Forfaitaire (+): ' . $tab['idemn'] . ' ' . $tab['Devis'] . '/j', 1, 0);
        $pdf->Cell($w50, 10, 'Supplément (+): ' . $tab['Bonus'] . ' ' . $tab['Devis'] . '/j', 1, 1);

        $pdf->Cell($w50, 10, 'Indemnité de déplacement (-): ' . $tab['Idemn_depl'], 1, 0);
        $pdf->Cell($w50, 10, 'Total indemnité (=): ' . $tab['totalIdemn'] . ' ' . $tab['Devis'], 1, 1);

        $pdf->setY(145);
        $pdf->Cell(20, 10, 'Autres: ', 0, 1);

        $pdf->setXY(30, 145);
        $pdf->Cell(80, 10,  'MOTIF', 1, 0, 'C');
        $pdf->Cell(80, 10, '' . 'MONTANT', 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $tab['motifdep01'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep01'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '  ' . $tab['motifdep02'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep02'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  '   ' . $tab['motifdep03'], 1, 0, 'L');
        $pdf->Cell(80, 10, '' . $tab['montdep03'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'Total autre ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $tab['totaldep'] . ' ' . $tab['Devis'], 1, 1, 'C');
        $pdf->setX(30);
        $pdf->Cell(80, 10,  'MONTANT TOTAL A PAYER ', 1, 0, 'C');
        $pdf->Cell(80, 10,   $tab['AllMontant'] . ' ' . $tab['Devis'], 1, 1, 'C');

        $pdf->setY(210);
        $pdf->Cell($w50, 10, 'Mode de paiement:  ' . $tab['libmodepaie'], 0, 0);
        $pdf->setX($w50 + 20);
        $pdf->Cell($w50, 10, $tab['mode'], 0, 1);


        // génération de fichier
        $Dossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/dom/';
        $pdf->Output($Dossier . $tab['NumDom'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
    }

    private function getHalfWidth(TCPDF $pdf)
    {
        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)

        $usable_width = $w_total - $margins['left'] - $margins['right'];
        return $usable_width / 2;
    }
}
