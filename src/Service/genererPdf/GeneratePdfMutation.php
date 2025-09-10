<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdfMutation extends GeneratePdf
{
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
     * Genere le PDF DEMANDE DE MUTATION (MUT)
     */
    public function genererPDF($tab)
    {
        $pdf = $this->pdf;
        // ... (le reste de la méthode genererPDF reste identique)

        $Dossier = ($_ENV['BASE_PATH_FICHIER'] ?? '') . '/mut/';
        $pdf->Output($Dossier . $tab['NumMut'] . '_' . $tab['codeAg_serv'] . '.pdf', 'F');
    }
}