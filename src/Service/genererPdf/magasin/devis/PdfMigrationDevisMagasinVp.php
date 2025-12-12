<?php

namespace App\Service\genererPdf\magasin\devis;

use App\Service\genererPdf\HeaderPdf;
use App\Service\genererPdf\GeneratePdf;


class PdfMigrationDevisMagasinVp extends GeneratePdf
{
    public function copyToDWMigrationDevisVpMagasin(string $fileName, string $numeroDevis): void
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'VERIFICATION_PRIX_MAGASIN/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'magasin/devis/' . $numeroDevis . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function genererPdf(array $data, string $filePath)
    {
        $pdf = new HeaderPdf(null);

        $font2 = "helvetica";

        $pdf->AddPage();

        $pdf->Ln(5, true);

        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Numéro devis : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['numero_devis'], 0, 1, 'L');

        $pdf->Ln(5, true);


        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Nom client : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['nom_client'], 0, 1, 'L');

        $pdf->Ln(5, true);

        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Succursale : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['succursale'], 0, 1, 'L');

        $pdf->Ln(5, true);


        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Service : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['service'], 0, 1, 'L');

        $pdf->Ln(5, true);
        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'date : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['date'], 0, 1, 'L');

        $pdf->Ln(5, true);

        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Total HT : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['total_ht'], 0, 1, 'L');

        $pdf->Ln(5, true);

        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Total TTC : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $data['total_ttc'], 0, 1, 'L');

        $pdf->Output($filePath, 'F');
    }
}
