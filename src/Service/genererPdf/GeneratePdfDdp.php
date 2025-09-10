<?php

namespace App\Service\genererPdf;

use App\Entity\ddp\DemandePaiement;
use App\Traits\ChaineCaractereTrait;
use TCPDF;

class GeneratePdfDdp extends GeneratePdf
{
    use ChaineCaractereTrait;

    private $pdf;

    public function __construct(
        TCPDF $pdf,
        string $baseCheminDuFichier = null,
        string $baseCheminDocuware = null
    ) {
        parent::__construct($baseCheminDuFichier, $baseCheminDocuware);
        $this->pdf = $pdf;
    }

    /**
     * Genere le PDF DEMANDE DE PAIEMENT (DDP)
     */
    public function genererPDF(DemandePaiement $data, string $cheminDeFichier)
    {
        $pdf = $this->pdf;

        $logoPath = ($_ENV['BASE_PATH_LONG'] ?? '') . '/Views/assets/henriFraise.jpg';

        $w_total = $pdf->GetPageWidth();
        $margins = $pdf->GetMargins();
        $usable_width = $w_total - $margins['left'] - $margins['right'];
        $w50 = $usable_width / 2;

        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        // ... (le reste de la méthode genererPDF reste identique)

        $pdf->Output($cheminDeFichier, 'F');
    }
}