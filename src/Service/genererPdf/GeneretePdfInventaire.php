<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use TCPDF;
class GeneretePdfInventaire extends GeneratePdf
{
    use FormatageTrait;
    /**
     * Genere le PDF INVENTAIRE
     */
    public function genererPDF(array $data)
    {
        $pdf = new TCPDF();

        $H_total = $pdf->getPageHeight();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_heigth = $H_total - $margins['top'] - $margins['bottom'] + 10;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->setPageOrientation('L');
        $pdf->SetAuthor('Votre Nom');
        $pdf->SetTitle('Écart sur inventaire');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Ajout du logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/henriFraise.jpg';
        $pdf->Image($logoPath, 10, 10, 50);
        $pdf->Ln(15);

        // Titre principal
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Écart sur inventaire', 0, 1, 'C');
        $pdf->Ln(2);

        // Date en haut à droite
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(250, 10);
        $pdf->Cell(0, 5, date('d/m/Y'), 0, 1, 'R');

        // Numéro de page en dessous
        $pdf->SetXY(250, 15);
        $pdf->Cell(0, 5, 'Page                                 ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 1, 'R');

        $pdf->Ln(15);
        // Sous-titre
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 10, 'INVENTAIRE N°:' . $data[0]['numinv'], 0, 1, 'C');
        $pdf->Cell(0, 10, 'du : ' . $data[0]['dateInv'], 0, 1, 'C');
        $pdf->Ln(5);

        // Création du tableau
        $pdf->SetFont('dejavusans', '', 6.5);
        $pdf->Cell(15, 6, 'CST', 1, 0, 'C');
        $pdf->Cell(30, 6, 'Référence', 1, 0, 'C');
        $pdf->Cell($usable_heigth - 225, 6, 'Description', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Casier', 1, 0, 'C');
        $pdf->Cell(30, 6, 'Qté théorique', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 1', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 2', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 3', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Écart', 1, 0, 'C');
        $pdf->Cell(35, 6, 'P.M.P', 1, 0, 'C');
        $pdf->Cell(35, 6, 'Montant écart', 1, 1, 'C');

        // Remplissage du tableau avec les données
        $pdf->SetFont('dejavusans', '', 6.5);
        $total = 0;
        foreach ($data as $row) {
            $montant_ecarts = str_replace('.', '', $row['montant_ajuste']);
            $total += (float)$montant_ecarts;
            $pdf->Cell(15, 6, $row['cst'], 1, 0, 'C');
            $pdf->Cell(30, 6, $row['refp'], 1, 0, 'C');
            $pdf->Cell($usable_heigth - 225, 6, $row['desi'], 1, 0, 'C');
            $pdf->Cell(20, 6, $row['casier'], 1, 0, 'C');
            $pdf->Cell(30, 6, $row['stock_theo'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_1'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_2'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_3'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['ecart'], 1, 0, 'C');
            $pdf->Cell(35, 6, str_replace('.', ' ', $row['pmp']), 1, 0, 'R');
            $pdf->Cell(35, 6, str_replace('.', ' ', $row['montant_ajuste']), 1, 1, 'R');
        }

        // Affichage du nombre de lignes
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(50, 7, 'Nombre de lignes : ' . count($data), 0, 0, 'L');

        // Affichage du total
        $pdf->Cell($usable_heigth - 130, 7, '', 0, 0);
        $pdf->Cell(35, 7, 'Total écart', 0, 0, 'R');
        $pdf->Cell(35, 7, str_replace('.', ' ', $this->formatNumber($total)), 0, 1, 'R');

        // Sortie du fichier PDF
        $pdf->Output('ecart_inventaire.pdf', 'I');
    }
}

