<?php
namespace App\Service\genererPdf;
use TCPDF;
class GeneretePdfBordereau extends GeneratePdf{
    public function genererPDF(array $data)
    {
        $pdf = new TCPDF();

        $W_total = $pdf->getPageWidth();  // Hauteur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $W_total - $margins['left'] - $margins['right'];

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Votre Nom');
        $pdf->SetTitle('Bordereau  de comptage');
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Numéro de page en dessous
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetXY($W_total-25, 5);
        $pdf->Cell(0, 5, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 1, 'R');

        // Ajout du logo
        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henriFraise.jpg';
        $pdf->Image($logoPath, 10, 12, 35);
        
        // Date en haut à droite
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetXY(250, 10);
        $pdf->Cell(0, 5, date('d/m/Y'), 0, 1, 'R'); 

        // Titre principal
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 5, 'Bordereau  de comptage', 0, 1, 'C');
        // Sous-titre
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Cell(0, 5, 'INVENTAIRE N°:' . $data[0]['numinv'], 0, 1, 'C');
        $pdf->Cell(0, 5, 'du : ' . $data[0]['dateinv'], 0, 1, 'C');
        $pdf->Ln();

        // Création du tableau
        $pdf->SetFont('dejavusans', '', 6.5);
        $pdf->Cell(15, 4, 'Ligne', 1, 0, 'C');
        $pdf->Cell(20, 4, 'Casier', 1, 0, 'C');
        $pdf->Cell(20, 4, 'CST', 1, 0, 'C');
        $pdf->Cell(20, 4, 'Référence', 1, 0, 'C');
        $pdf->Cell($usable_width - 155, 4, 'Désignation', 1, 0, 'C');
        $pdf->Cell(15, 4, 'Qté Phy.', 1, 0, 'C');
        $pdf->Cell(15, 4, 'All.', 1, 0, 'C');
        $pdf->Cell(15, 4, 'Qté All.', 1, 0, 'C');
        $pdf->Cell(35, 4, 'Observation', 1, 1, 'C');

        // Tri des données par CST
        usort($data, function ($a, $b) {
            return $a['numBordereau'] - $b['numBordereau'];
        });

        // Remplissage du tableau avec rupture par CST
        $pdf->SetFont('dejavusans', '', 5.5);
        $ref_count = 0;
        $lastBordereau = null;
        foreach ($data as $row) {
            if ($lastBordereau !== $row['numBordereau']) {
                $lastBordereau = $row['numBordereau'];
                $pdf->SetFont('dejavusans', 'B', 5.5);
                $pdf->Cell(0, 4, "BORDEREAU : " . strtoupper($lastBordereau), 1, 1, 'C');
                $pdf->SetFont('dejavusans', '', 5.5);
            }
            $ref_count ++;
            // $montant_ecarts = str_replace('.', '', $row['montant_ajuste']);
            // $total += (float)$montant_ecarts;
            $pdf->Cell(15, 4, $row['ligne'], 1, 0, 'C');
            $pdf->Cell(20, 4, $row['casier'], 1, 0, 'C');
            $pdf->Cell(20, 4, $row['cst'], 1, 0, 'C');
            $pdf->Cell(20, 4, $row['refp'], 1, 0, 'C');
            $pdf->Cell($usable_width - 155, 4, $row['descrip'], 1, 0, 'C');
            $pdf->Cell(15, 4, '', 1, 0, 'C');
            $pdf->Cell(15, 4, $row['qte_alloue'] !== "0" ? "X":"", 1, 0, 'C');
            $pdf->Cell(15, 4, $row['qte_alloue'] === "0" ? "":$row['qte_alloue'], 1, 0, 'C');
            $pdf->Cell(35, 4, '', 1, 1, 'R');
        }

        // Affichage du nombre de lignes
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Cell(50, 5.5, 'Nombre de reférence: ' . $ref_count, 0, 0, 'L');

        // Sortie du fichier PDF
        $pdf->Output('bordereau_de_comptage.pdf', 'I');
    }
}