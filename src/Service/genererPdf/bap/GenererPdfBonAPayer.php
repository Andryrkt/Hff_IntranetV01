<?php

namespace App\Service\genererPdf\bap;

use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\GeneratePdf;

class GenererPdfBonAPayer extends GeneratePdf
{
    /**
     * Fonction pour générer le PDF du bon à payer
     */
    public function genererPageDeGarde(array $infoBC, array $infoValidationBC, array $infoMateriel, array $dataRecapOR, DemandeAppro $demandeAppro, array $infoFacBl): string
    {
        return '';
    }
}
