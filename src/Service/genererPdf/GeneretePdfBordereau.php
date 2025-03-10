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
        $pdf->SetTitle('Bordereua de comptage');
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Ajout du logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/henriFraise.jpg';
        $pdf->Image($logoPath, 10, 10, 50);
        $pdf->Ln(15);

        // Titre principal
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Bordereua de comptage', 0, 1, 'C');
        $pdf->Ln(2);

        // Date en haut à droite
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(250, 10);
        $pdf->Cell(0, 5, date('d/m/Y'), 0, 1, 'R');

        // Numéro de page en dessous
        $pdf->SetXY(250, 15);
        $pdf->Cell(0, 5, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 1, 'R');

        $pdf->Ln(15);

        // Sous-titre
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 10, 'INVENTAIRE N°:' . $data[0]['numinv'], 0, 1, 'C');
        $pdf->Cell(0, 10, 'du : ' . $data[0]['dateinv'], 0, 1, 'C');
        $pdf->Ln(5);

        // Création du tableau
        $pdf->SetFont('dejavusans', '', 6.5);
        $pdf->Cell(15, 6, 'Ligne', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Casier', 1, 0, 'C');
        $pdf->Cell(20, 6, 'CST', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Référence', 1, 0, 'C');
        $pdf->Cell($usable_width - 155, 6, 'Désignation', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Qté Phy.', 1, 0, 'C');
        $pdf->Cell(15, 6, 'All.', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Qté All.', 1, 0, 'C');
        $pdf->Cell(35, 6, 'Observation', 1, 1, 'C');

        // Tri des données par CST
        usort($data, function ($a, $b) {
            return $a['numBordereau'] - $b['numBordereau'];
        });

        // Remplissage du tableau avec rupture par CST
        $pdf->SetFont('dejavusans', '', 6.5);
        $ref_count = 0;
        $lastBordereau = null;
        foreach ($data as $row) {
            if ($lastBordereau !== $row['numBordereau']) {
                $lastBordereau = $row['numBordereau'];
                $pdf->SetFont('dejavusans', 'B', 7);
                $pdf->Cell(0, 6, "BORDEREAU : " . strtoupper($lastBordereau), 1, 1, 'C');
                $pdf->SetFont('dejavusans', '', 6.5);
            }
            $ref_count ++;
            // $montant_ecarts = str_replace('.', '', $row['montant_ajuste']);
            // $total += (float)$montant_ecarts;
            $pdf->Cell(15, 6, $row['ligne'], 1, 0, 'C');
            $pdf->Cell(20, 6, $row['casier'], 1, 0, 'C');
            $pdf->Cell(20, 6, $row['cst'], 1, 0, 'C');
            $pdf->Cell(20, 6, $row['refp'], 1, 0, 'C');
            $pdf->Cell($usable_width - 155, 6, $row['descrip'], 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_alloue'] !== "0" ? "X":"", 1, 0, 'C');
            $pdf->Cell(15, 6, '', 1, 0, 'C');
            $pdf->Cell(15, 6, $row['qte_alloue'] === "0" ? "":$row['qte_alloue'], 1, 0, 'C');
            $pdf->Cell(35, 6, '', 1, 1, 'R');
        }

        // Affichage du nombre de lignes
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(50, 7, 'Nombre de reférence: ' . $ref_count, 0, 0, 'L');

        // Sortie du fichier PDF
        $pdf->Output('bordereau_de_comptage.pdf', 'I');
    }
}