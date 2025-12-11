<?php

namespace App\Service\genererPdf\magasin\bc;

use App\Service\genererPdf\HeaderPdf;
use App\Entity\admin\utilisateur\User;
use App\Model\magasin\bc\BcMagasinDto;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGeneratorFlexible;

class GeneratePdfBcMagasin extends GeneratePdf
{
    /**
     * copie la page de garde du BC magasin dans docuware
     *
     * @param string $fileName
     * @param string $numeroDevis
     * @return void
     */
    public function copyToDWBcMagasin(string $fileName, string $numeroDevis): void
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'BC MAGASIN/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'magasin/devis/' . $numeroDevis . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    /**
     * creation du pdf de la page de garde du BC magasin
     *
     * @param User $user
     * @param BcMagasinDto $dto
     * @param string $filePath
     * @param float $montantDevis
     * @return void
     */
    public function generer(User $user, BcMagasinDto $dto, string $filePath, float $montantDevis): void
    {
        $pdf = new HeaderPdf($user->getNomUtilisateur() . ' - ' . $user->getMail());
        $generatorFlexible = new PdfTableGeneratorFlexible();

        $font2 = "helvetica";

        $pdf->AddPage();

        $pdf->Ln(2, true);
        // numero Devis
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'Devis', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, ': ' . $dto->numeroDevis, 0, 0, 'L');
        $pdf->Ln(7, true);

        // client : code client - nom client
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'Client', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(90, 10, ': ' . $dto->codeClient . ' - ' . $dto->nomClient, 0, 0, 'L');
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(35, 10, 'Mode de paiement : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $dto->modePayement, 0, 0, 'L');
        $pdf->Ln(7, true);

        //numero BC
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'BC', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(90, 10, ': ' . $dto->numeroBc, 0, 0, 'L');
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(35, 10, 'Date BC : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10,  $dto->dateBc->format('d/m/Y'), 0, 0, 'L');
        $pdf->Ln(7, true);

        // montant Devis
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'Montant devis', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, ': ' . number_format((float) $montantDevis, 2, ',', '.'), 0, 0, 'L');
        $pdf->Ln(7, true);

        //montant BC
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'Montant BC', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, ': ' . number_format(str_replace([' ', ','], ['', '.'], $dto->montantBc), 2, ',', '.'), 0, 1, 'L');
        // $pdf->Ln(5, true);

        // observation
        $pdf->setFont($font2, 'B', 10);
        $pdf->Cell(30, 6, 'Observation', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont($font2, '', 10);
        $pdf->MultiCell(164, 100, ': ' . $dto->observation, 0, '', 0, 0, '', '', true);
        $pdf->Ln(50, true);

        // tableau des lignes
        $pdf->setFont($font2, 'B', 10);
        $this->addTitle($pdf, 'Liste pièces avec les actions à faire par le validateur : en ligne les pièces et les divers champs en colonnes');
        $header = $this->headerTableau();
        $pdf->setFont($font2, '', 10);
        $html1 = $generatorFlexible->generateTable($header, $dto->lignes, []);
        $pdf->writeHTML($html1, true, false, true, false, '');

        $pdf->Output($filePath, 'F');
    }

    private function headerTableau(): array
    {
        $formatterBooleenIcone = function ($value) {
            return $value ? 'OUI' : '';
        };

        $formatterPourcentage = function ($value) {
            return $value . '%';
        };

        $styleBoldCenter = 'font-weight: bold; text-align: center;';
        $styleBoldLeft = 'font-weight: bold; text-align: left;';
        $styleBoldRight = 'font-weight: bold; text-align: right;';

        return [
            [
                'key' => 'numeroLigne',
                'label' => 'N°',
                'width' => 30,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'constructeur',
                'label' => 'CST',
                'width' => 30,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'ref',
                'label' => 'Réf.',
                'width' => 40,
                'style' => $styleBoldLeft,
            ],
            [
                'key' => 'designation',
                'label' => 'Désignation',
                'width' => 115,
                'style' => $styleBoldLeft,
            ],
            [
                'key' => 'qte',
                'label' => 'Qté',
                'width' => 20,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'prixHt',
                'label' => 'PU',
                'width' => 50,
                'style' => $styleBoldRight,
                'type' => 'number',
            ],
            [
                'key' => 'montantNet',
                'label' => 'Montant',
                'width' => 70,
                'style' => $styleBoldRight,
                'type' => 'number',
            ],
            [
                'key' => 'remise1',
                'label' => 'Remise 1',
                'width' => 37,
                'style' => $styleBoldCenter,
                'type' => 'number',
                'formatter' => $formatterPourcentage,
            ],
            [
                'key' => 'remise2',
                'label' => 'Remise 2',
                'width' => 37,
                'style' => $styleBoldCenter,
                'type' => 'number',
                'formatter' => $formatterPourcentage,
            ],
            [
                'key' => 'ras',
                'label' => 'RAS',
                'width' => 35,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ],
            [
                'key' => 'qteModifier',
                'label' => 'Qté à Modifier',
                'width' => 35,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ],
            [
                'key' => 'supprimer',
                'label' => 'Ligne à supprimer',
                'width' => 40,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ]
        ];
    }
}
