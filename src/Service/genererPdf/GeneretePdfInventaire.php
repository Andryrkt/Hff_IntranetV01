<?php
namespace App\Service\genererPdf;
use TCPDF;
class PDF extends TCPDF {
    public function Header() {
        $this->Image('logo.png', 10, 10, 40);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 10, 'Ecart sur inventaire', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'INVENTAIRE N° : 1916', 0, 1, 'C');
        $this->Cell(0, 10, 'du : 13/02/2025', 0, 1, 'C');
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->SetMargins(10, 30, 10);
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 10);
$html = '<table border="1" cellpadding="5">
    <tr>
        <th>CST</th><th>Référence</th><th>Description</th><th>Casier</th><th>Qté théorique</th>
        <th>Qté comptée</th><th>Ecart</th><th>P.M.P</th><th>Montant écart</th>
    </tr>';

$data = [
    ['CAT', '2441250', 'BELT KT', '95A11', 2, 0, -2, '2 658 159,28', '-5 316 318,56'],
    ['CAT', '7W6129', 'V-BELT', '95A11', 101, 100, -1, '132 095,67', '-132 095,67'],
    ['CAT', '8N6710', 'VEE BELT SET', '95A25', 9, 7, -2, '1 186 529,67', '-2 373 059,34'],
    ['CAT', '8J6245', 'RING', '95L9-7', 4, 8, 4, '120 050,48', '480 201,92'],
];

foreach ($data as $row) {
    $html .= '<tr>';
    foreach ($row as $cell) {
        $html .= '<td>' . $cell . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Total écart: -7 341 271,65', 0, 1, 'R');
$pdf->Output('inventaire.pdf', 'I');
