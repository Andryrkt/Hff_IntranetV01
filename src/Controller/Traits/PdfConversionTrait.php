<?php

namespace App\Controller\Traits;

use App\Service\fichier\ConversionPdfService;

trait PdfConversionTrait
{
    private function ConvertirLesPdf(array $tousLesFichersAvecChemin)
    {
        // conversion Ghostscript seulement pour les fichiers que FPDI ne sait pas lire (voir ConversionPdfService)
        return (new ConversionPdfService())->convertirLesPdf($tousLesFichersAvecChemin);
    }
}
