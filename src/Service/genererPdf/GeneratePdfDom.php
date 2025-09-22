<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdfDom extends GeneratePdf
{
    private $pdf;

    public function __construct(
        TCPDF $pdf,
        string $baseCheminDuFichier = null
    ) {
        parent::__construct($baseCheminDuFichier);
        $this->pdf = $pdf;
    }

    /**
     * Genere le PDF DEMANDE D'ORDRE DE MISSION (DOM)
     */
    public function genererPDF(array $tab)
    {
        $pdf = $this->pdf;
        // ... (le reste de la méthode genererPDF reste identique)

        $Dossier = ($_ENV['BASE_PATH_FICHIER'] ?? '') . '/dom/';
        $pdf->Output($Dossier . $tab['NumDom'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
    }

    private function getHalfWidth(TCPDF $pdf)
    {
        $w_total = $pdf->GetPageWidth();
        $margins = $pdf->GetMargins();
        $usable_width = $w_total - $margins['left'] - $margins['right'];
        return $usable_width / 2;
    }
}
