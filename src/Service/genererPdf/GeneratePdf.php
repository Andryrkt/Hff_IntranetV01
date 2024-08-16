<?php

namespace App\Service\genererPdf;

class GeneratePdf 
{
     /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {

        // if (substr($NumDom, 0, 3) === 'DOM') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\ORDERE DE MISSION\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // } else if (substr($NumDom, 0, 3) === 'BDM') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\MOUVEMENT MATERIEL\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // } else if (substr($NumDom, 0, 3) === 'CAS') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\CASIER\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // }  else if (substr($NumDom, 0, 3) === 'DIT') {
        //     $cheminFichierDistant = '\\\\192.168.0.15\\hff_pdf\\DOCUWARE\\DEVELOPPEMENT\\DIT\\' . $NumDom . '_' . $codeAg_serv . '.pdf';
        //     // $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        // }

        $cheminFichierDistant = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = 'C:/wamp64/www/Upload/' . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }
}