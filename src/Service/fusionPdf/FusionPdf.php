<?php

namespace App\Service\fusionPdf;

use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF;

class FusionPdf
{
    /**
     * FONCTION GENERALE POUR FUSIONNER DES PDF
     *
     * @param array $files
     * @param [type] $outputFile
     * @return void
     */
    public function mergePdfs(array $files, $outputFile) {
        // Instancier FPDI
        $pdf = new FPDI();

        // Désactiver les en-têtes et pieds de page automatiques
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Boucle sur chaque fichier PDF à fusionner
        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $specs = $pdf->getTemplateSize($tplIdx);
                $pdf->AddPage($specs['orientation'], [$specs['width'], $specs['height']]);
                $pdf->useTemplate($tplIdx);
            }
        }

        // Enregistrer le fichier PDF fusionné
        $pdf->Output($outputFile, 'F');
    }


    public function mergePdfsAndImages($files, $outputFile) {
        // Créer une instance de TCPDF pour le document final
        $pdf = new Fpdi();
    
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
            if ($ext === 'pdf') {
                $pageCount = $pdf->setSourceFile($file);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tplId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tplId);
                }
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $pdf->AddPage();
                $pdf->Image($file, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
            }
        }
    
        // Sauvegarder le fichier PDF fusionné
        $pdf->Output($outputFile, 'F');
    }
}