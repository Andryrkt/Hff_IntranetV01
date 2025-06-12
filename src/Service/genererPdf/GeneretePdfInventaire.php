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

        // numero de Page
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->setXY($H_total - 25, 5);
        $pdf->Cell(15, 5, 'Page  ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 1, 'R');

        // Ajout du logo
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henriFraise.jpg';
        $pdf->Image($logoPath, 10, 12, 35);

        // Date en haut à droite
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->setY(12);
        $pdf->Cell(0, 5, date('d/m/Y'), 0, 1, 'R');

        // Titre principal
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->Cell(0, 5, 'Écart sur inventaire', 0, 1, 'C');

        // Sous-titre
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Cell(0, 5, 'INVENTAIRE N°:' . $data[0]['numinv'], 0, 1, 'C');
        $pdf->Cell(0, 5, 'du : ' . $data[0]['dateInv'], 0, 1, 'C');
        $pdf->Ln();

        // Création du tableau
        $pdf->SetFont('dejavusans', '', 6.5);
        $pdf->Cell(15, 6, 'CST', 1, 0, 'C');
        $pdf->Cell(30, 6, 'Référence', 1, 0, 'C');
        $pdf->Cell($usable_heigth - 225, 6, 'Description', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Casier', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Qté théo', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 1', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 2', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Cpt 3', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Écart', 1, 0, 'C');
        $pdf->Cell(30, 6, 'P.M.P', 1, 0, 'C');
        $pdf->Cell(30, 6, 'Mont. écart', 1, 0, 'C');
        $pdf->Cell(25, 6, '% Mont. écart', 1, 1, 'C');

        // Remplissage du tableau avec les données
        $pdf->SetFont('dejavusans', '', 6.5);
        $totalMont_ecart = 0;
        $totalMont_Pmp = 0;
        foreach ($data as $row) {
            $totalMont_ecart += (int)$row['montant_ajuste'];
            $totalMont_Pmp += $row['pmp'];
            $pdf->Cell(15, 6, $row['cst'], 1, 0, 'L');
            $pdf->Cell(30, 6, $row['refp'], 1, 0, 'L');
            $pdf->Cell($usable_heigth - 225, 6, $row['desi'], 1, 0, 'L');
            $pdf->Cell(20, 6, $row['casier'], 1, 0, 'L');
            $pdf->Cell(20, 6, $row['stock_theo'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_1'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_2'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_comptee_3'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['ecart'], 1, 0, 'C');
            $pdf->Cell(30, 6, str_replace('.', ' ', $this->formatNumber($row['pmp'])), 1, 0, 'R');
            $pdf->Cell(30, 6, str_replace('.', ' ', $this->formatNumber($row['montant_ajuste'])), 1, 0, 'C');
            $pdf->Cell(25, 6, $row['pourcentage_ecart'], 1, 1, 'R');
        }

        // Affichage du nombre de lignes
        $pdf->SetFont('dejavusans', '', 6.5);
        $pdf->Cell(50, 7, 'Nombre de lignes : ' . count($data), 0, 0, 'L');

        // Affichage du total
        $pdf->Cell($usable_heigth - 155, 7, '', 0, 0);
        $pdf->Cell(25, 7, 'Total écart', 0, 0, 'R');
        $pdf->Cell(30, 7, str_replace('.', ' ', $this->formatNumber($totalMont_Pmp)), 1, 0, 'R');
        $pdf->Cell(30, 7, str_replace('.', ' ', $this->formatNumber($totalMont_ecart)), 1, 1, 'C');

        // Sortie du fichier PDF
        $pdf->Output('ecart_inventaire.pdf', 'I');
    }
}
