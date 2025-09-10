<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\AcSoumis;

class GenererPdfAcSoumis extends GeneratePdf
{
    use FormatageTrait;

    private $pdf;

    public function __construct(
        HeaderFooterAcPdf $pdf,
        string $baseCheminDuFichier = null,
        string $baseCheminDocuware = null
    ) {
        parent::__construct($baseCheminDuFichier, $baseCheminDocuware);
        $this->pdf = $pdf;
    }

    function genererPdfAc(AcSoumis $acSoumis, string $numeroDunom, string $numeroVersionMax, $nomFichier)
    {
        $pdf = $this->pdf;
        // ... (le reste de la méthode reste identique, sauf la première ligne)

        $Dossier = ($_ENV['BASE_PATH_FICHIER'] ?? '') . '/dit/ac_bc/';
        $filePath = $Dossier . $nomFichier;
        $pdf->Output($filePath, 'F');
    }
}