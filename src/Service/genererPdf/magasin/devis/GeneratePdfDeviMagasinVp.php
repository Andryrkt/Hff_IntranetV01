<?php

namespace App\Service\genererPdf\magasin\devis;

use App\Service\genererPdf\HeaderPdf;
use App\Entity\admin\utilisateur\User;
use App\Service\genererPdf\GeneratePdf;
use App\Entity\magasin\devis\DevisMagasin;

class GeneratePdfDeviMagasinVp extends GeneratePdf
{
    /**
     * copie la page de garde fusionner du devis magasin dans docuware
     *
     * @param string $fileName
     * @param string $numeroDevis
     * @return void
     */
    public function copyToDWDevisVpMagasin(string $fileName, string $numeroDevis): void
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'VERIFICATION_PRIX_MAGASIN/' . $fileName;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'magasin/devis/' . $numeroDevis . '/' . $fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function genererPdf(User $user, DevisMagasin $devisMagasin, string $filePath)
    {
        if ($devisMagasin->getEstValidationPm() == false) {
            $tacheValidateur = 'AUTOVALIDATION';
        } else {
            $tacheValidateur = $devisMagasin->getTacheValidateur();
        }

        $pdf = new HeaderPdf(null);
        // $font1 = "pdfatimesbi";
        $font2 = "helvetica";

        $pdf->AddPage();
        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(30, 10, 'Commercial : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $user->getNomUtilisateur() . ' - ' . $user->getMail(), 0, 1, 'L');

        $pdf->Ln(5, true);

        $pdf->SetFont($font2, 'B', 12);
        $pdf->Cell(63, 10, 'Opération à faire sur le devis : ', 0, 0, 'L');
        $pdf->SetFont($font2, '', 10);
        $pdf->Cell(0, 10, $tacheValidateur, 0, 1, 'L');

        $pdf->Output($filePath, 'F');
    }
}
