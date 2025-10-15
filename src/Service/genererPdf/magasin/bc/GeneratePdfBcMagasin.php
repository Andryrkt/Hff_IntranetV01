<?php

namespace App\Service\genererPdf\magasin\bc;

use App\Service\genererPdf\HeaderPdf;
use App\Entity\admin\utilisateur\User;
use App\Model\magasin\bc\BcMagasinDto;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGenerator;

class GeneratePdfBcMagasin extends GeneratePdf
{
    public function generer(User $user, BcMagasinDto $dto, string $filePath, float $montantDevis): void
    {
        $pdf = new HeaderPdf($user->getNomUtilisateur() . ' - ' . $user->getMail());
        $generator = new PdfTableGenerator();

        $font1 = "pdfatimesbi";
        $font2 = "helvetica";

        $pdf->AddPage();

        // observation
        $pdf->setFont($font2, 'B', 12);
        $pdf->Cell(30, 6, 'Observation : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont($font2, '', 9);
        $pdf->MultiCell(164, 100, $dto->observation, 0, '', 0, 0, '', '', true);
        $pdf->Ln(5, true);

        // numero Devis
        $pdf->SetFont($font1, 'B', 12);
        $pdf->Cell(30, 10, 'Devis : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $dto->numeroDevis, 0, 1, 'L');
        $pdf->Ln(5, true);

        //numero BC
        $pdf->SetFont($font1, 'B', 12);
        $pdf->Cell(30, 10, 'BC : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $dto->numeroBc, 0, 1, 'L');
        $pdf->Ln(5, true);

        // montant Devis
        $pdf->SetFont($font1, 'B', 12);
        $pdf->Cell(30, 10, 'Montant devis : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $montantDevis, 0, 1, 'L');
        $pdf->Ln(5, true);

        //montant BC
        $pdf->SetFont($font1, 'B', 12);
        $pdf->Cell(30, 10, 'Montant BC : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $dto->montantBc, 0, 1, 'L');
        $pdf->Ln(5, true);

        // tableau des lignes
        $this->addTitle($pdf, 'Liste pièces avec les actions à faire par le validateur : en ligne les pièces et les divers champs en colonnes');
        $pdf->SetTextColor(0, 0, 0);
        $header = [
            ['key' => 'reference',   'label' => 'Référence',   'width' => 110, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'designation', 'label' => 'Désignation', 'width' => 190, 'style' => 'font-weight: bold; text-align: left;'],
            ['key' => 'pu1',         'label' => 'PU',          'width' => 80,  'style' => 'font-weight: bold; text-align: right;'],
            ['key' => 'qte',         'label' => 'Qté',         'width' => 60,  'style' => 'font-weight: bold; text-align: center;'],
            ['key' => 'mttTotal',    'label' => 'Montant',     'width' => 100, 'style' => 'font-weight: bold; text-align: right;'],
        ];
        $html1 = $generator->generateTable($header, $dto->lignes, []);
        $pdf->writeHTML($html1, true, false, true, false, '');

        $pdf->Output($filePath, 'F');
    }
}
