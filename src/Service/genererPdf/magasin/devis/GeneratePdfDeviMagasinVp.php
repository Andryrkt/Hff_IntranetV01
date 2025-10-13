<?php

namespace App\Service\genererPdf\magasin\devis;

use App\Service\genererPdf\HeaderPdf;
use App\Entity\admin\utilisateur\User;
use App\Service\genererPdf\GeneratePdf;
use App\Entity\magasin\devis\DevisMagasin;

class GeneratePdfDeviMagasinVp extends GeneratePdf
{
    public function genererPdf(User $user, DevisMagasin $devisMagasin, string $filePath)
    {
        $pdf = new HeaderPdf($user->getMail());
        $font = "pdfatimesbi";

        $pdf->AddPage();
        $pdf->SetFont($font, 'B', 12);
        $pdf->Cell(35, 10, 'Commercial', 0, 1, 'L');
        $pdf->SetFont($font, '', 10);
        $pdf->Cell(0, 10, $user->getNomUtilisateur() . ' - ' . $user->getMail(), 0, 1, 'L');

        $pdf->Ln(7, true);

        $pdf->SetFont($font, 'B', 12);
        $pdf->Cell(50, 10, 'Opération à faire sur le devis', 0, 1, 'L');
        $pdf->SetFont($font, '', 10);
        $pdf->Cell(0, 10, $devisMagasin->getTacheValidateur(), 0, 1, 'L');

        $pdf->Output($filePath, 'I' /* Afficher dans le navigateur */);
    }
}
