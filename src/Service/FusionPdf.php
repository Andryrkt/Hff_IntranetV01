<?php

namespace App\Service;

use setasign\Fpdi\Tcpdf\Fpdi;



class FusionPdf
{
    /**
     * Fusion du Pdf avec les 2 Pièce Joints
     */
    public function genererFusion($FichierDom, $FichierAttache01, $FichierAttache02)
    {
        $pdf01 = new Fpdi();
        $chemin01 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . $FichierDom;
        $pdf01->setSourceFile($chemin01);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $FichierAttache01;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin02);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin03 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $FichierAttache02;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin03);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        // Sauvegarder le PDF fusionné
        $pdf01->Output($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Fusion/' . $FichierDom, 'I');
        // $pdf01->Output('C:/DOCUWARE/ORDRE_DE_MISSION/' . $FichierDom, 'F');
    }
    /**
     * Fusion du Pdf avec un Pièce Joint
     */
    public function genererFusion1($FichierDom, $FichierAttache01)
    {
        $pdf01 = new Fpdi();
        $chemin01 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Upload/' . $FichierDom;
        $pdf01->setSourceFile($chemin01);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        $chemin02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $FichierAttache01;
        // Ajouter le deuxième fichier PDF
        $pdf01->setSourceFile($chemin02);
        $templateId = $pdf01->importPage(1);
        $pdf01->addPage();
        $pdf01->useTemplate($templateId);

        // Sauvegarder le PDF fusionné
        $pdf01->Output($_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Fusion/' . $FichierDom, 'I');
        // $pdf01->Output('C:/DOCUWARE/ORDRE_DE_MISSION/' . $FichierDom, 'F');
    }
}
