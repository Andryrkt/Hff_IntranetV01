<?php

namespace App\Controller\Traits;

use DateTime;

trait DomTrait
{
    private function changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB)
    {
        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $filename01;
        move_uploaded_file($filetemp01, $Upload_file);
        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $filename02;
        move_uploaded_file($filetemp02, $Upload_file02);
        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
        if (!empty($filename02)) {
            //echo 'fichier02';
            $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
        } else {
            $this->fusionPdf->genererFusion1($FichierDom, $filename01);
            //echo 'echo non';
        }
    }

    private function changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB)
    {
        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $filename01;
        move_uploaded_file($filetemp01, $Upload_file);
        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/src/Controller/pdf/' . $filename02;
        move_uploaded_file($filetemp02, $Upload_file02);
        return $NumDom . '_' . $codeAg_servDB . '.pdf';
    }

    
    private function verifierSiDateExistant(string $matricule, string $dateDebutInput, string $dateFinInput): bool
    {
        $Dates = $this->DomModel->getInfoDOMMatrSelet($matricule);
        $trouve = false; // Variable pour indiquer si la date est trouvée

        // Parcourir chaque élément du tableau
        foreach ($Dates as $periode) {
            // Convertir les dates en objets DateTime pour faciliter la comparaison
            $dateDebut = new DateTime($periode['Date_Debut']);
            $dateFin = new DateTime($periode['Date_Fin']);
            $dateDebutInputObj = new DateTime($dateDebutInput); // Correction de la variable
            $dateFinInputObj = new DateTime($dateFinInput); // Correction de la variable

            // Vérifier si la date à vérifier est comprise entre la date de début et la date de fin
            if (($dateFinInputObj >= $dateDebut && $dateFinInputObj <= $dateFin) || ($dateDebutInputObj >= $dateDebut && $dateDebutInputObj <= $dateFin)) { // Correction des noms de variables
                $trouve = true;
                return $trouve;
            }
        }

        // Vérifier si aucune correspondance n'est trouvée
        return $trouve;
    }
    
    private function insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom)
    {
        $this->DomModel->InsertDom($tabInsertionBdInterne);

        //echo 'ie ambany 500000';
        $this->genererPdf->genererPDF($tabInterne);

        $this->DomModel->modificationDernierIdApp($NumDom,'DOM');
    }

    
}