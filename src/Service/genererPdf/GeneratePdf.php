<?php

namespace App\Service\genererPdf;

class GeneratePdf 
{
     /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDw($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/oRValidation_' .$numeroOR.'_'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vor/oRValidation_' .$numeroOR.'_'.$numeroVersion . '.pdf';
       copy($cheminDestinationLocal, $cheminFichierDistant);
    }  

    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/factureValidation_' .$numeroOR.'_'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vfac/factureValidation_' .$numeroOR.'_'.$numeroVersion . '.pdf';
       copy($cheminDestinationLocal, $cheminFichierDistant);
    } 
        
    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/RI_' .$numeroOR.'_'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/vri/RI_' .$numeroOR.'_'.$numeroVersion . '.pdf';
       copy($cheminDestinationLocal, $cheminFichierDistant);
    } 
}