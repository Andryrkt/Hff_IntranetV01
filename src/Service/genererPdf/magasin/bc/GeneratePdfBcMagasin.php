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
     * copie la page de garde du devis magasin dans docuware
     *
     * @param string $fileName
     * @param string $numeroDevis
     * @return void
     */
    public function copyToDWBcMagasin(string $fileName, string $numeroDevis): void
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $fileName;
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

        // observation
        $pdf->setFont($font2, 'B', 10);
        $pdf->Cell(30, 6, 'Observation', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont($font2, '', 10);
        $pdf->MultiCell(164, 100, ': ' . $dto->observation, 0, '', 0, 0, '', '', true);
        $pdf->Ln(5, true);

        // numero Devis
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'Devis', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, ': ' . $dto->numeroDevis, 0, 0, 'L');
        $pdf->Ln(7, true);

        //numero BC
        $pdf->SetFont($font2, 'B', 10);
        $pdf->Cell(30, 10, 'BC', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, ': ' . $dto->numeroBc, 0, 0, 'L');
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
        $pdf->Ln(7, true);

        // tableau des lignes
        $this->addTitle($pdf, 'Liste pièces avec les actions à faire par le validateur : en ligne les pièces et les divers champs en colonnes');
        $pdf->SetTextColor(0, 0, 0);
        $header = $this->headerTableau();
        $html1 = $generatorFlexible->generateTable($header, $dto->lignes, []);
        $pdf->writeHTML($html1, true, false, true, false, '');

        $pdf->Output($filePath, 'F');
    }

    private function headerTableau(): array
    {
        $formatterBooleenIcone = function ($value) {
            return $value ? 'OUI' : '';
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
                'label' => 'Constructeur',
                'width' => 30,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'ref',
                'label' => 'Référence',
                'width' => 40,
                'style' => $styleBoldLeft,
            ],
            [
                'key' => 'designation',
                'label' => 'Désignation',
                'width' => 150,
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
                'label' => '%remise1',
                'width' => 30,
                'style' => $styleBoldCenter,
                'type' => 'number',
            ],
            [
                'key' => 'remise2',
                'label' => '%remise2',
                'width' => 30,
                'style' => $styleBoldCenter,
                'type' => 'number',
            ],
            [
                'key' => 'ras',
                'label' => 'ras',
                'width' => 30,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ],
            [
                'key' => 'qteModifier',
                'label' => 'qteModifier',
                'width' => 30,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ],
            [
                'key' => 'supprimer',
                'label' => 'supprimer',
                'width' => 30,
                'style' => $styleBoldCenter,
                'formatter' => $formatterBooleenIcone,
            ]
        ];
    }
}
