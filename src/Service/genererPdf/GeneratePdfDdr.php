<?php

namespace App\Service\genererPdf;

use App\Entity\ddp\DemandePaiement;
use App\Traits\ChaineCaractereTrait;
use TCPDF;

class GeneratePdfDdr extends GeneratePdf
{
    use ChaineCaractereTrait;

    private $pdf;

    public function __construct(
        TCPDF $pdf,
        string $baseCheminDuFichier = null
    ) {
        parent::__construct($baseCheminDuFichier);
        $this->pdf = $pdf;
    }

    /**
     * Genere le PDF DEMANDE DE PAIEMENT (DDP)
     */
    public function genererPDF(DemandePaiement $data, $email, $numDdr, string $cheminDeFichier)
    {
        $pdf = $this->pdf;

        $logoPath = ($_ENV['BASE_PATH_LONG'] ?? '') . '/Views/assets/henriFraise.jpg';

        // ... (le reste de la méthode genererPDF reste identique)

        $pdf->Output($cheminDeFichier, 'F');
    }
}
