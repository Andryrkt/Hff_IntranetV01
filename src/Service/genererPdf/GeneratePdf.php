<?php

namespace App\Service\genererPdf;

use TCPDF;

class GeneratePdf
{
    protected const DOCUWARE = "C:/DOCUWARE_TEST";
    protected const UPLOAD = "C:/wamp64/www/Upload_TEST";

    /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {
        $cheminFichierDistant = self::DOCUWARE . '/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = self::UPLOAD . '/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDw($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::DOCUWARE . '/ORDRE_DE_MISSION/oRValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = self::UPLOAD . '/vor/oRValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::DOCUWARE . '/ORDRE_DE_MISSION/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = self::UPLOAD . '/vfac/factureValidation_' . $numeroOR . '_' . $numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::DOCUWARE . '/RAPPORT_INTERVENTION/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf';
        $cheminDestinationLocal = self::UPLOAD . '/vri/RI_' . $numeroOR . '-' . $numeroVersion . '.pdf'; // avec tiret 6
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    public function copyToDWCdeSoumis($fileName)
    {
        $cheminFichierDistant = self::DOCUWARE . '/ORDRE_DE_MISSION/' . $fileName;
        $cheminDestinationLocal = self::UPLOAD . '/cde/' . $fileName;
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    /** 
     * Méthode pour générer le PDF créé
     * 
     * @param string $fileName nom du fichier pdf avec son dossier
     *  Exemple: dom/num_dom
     */
    protected function OutputPdf(TCPDF $pdf, string $fileName)
    {
        $pdf->Output(self::UPLOAD . "/$fileName.pdf", 'F');
    }
}
