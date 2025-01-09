<?php

namespace App\Service\genererPdf;

class GeneratePdf 
{
    const  BASE_CHEMIN_DU_FICHIER = 'C:/wamp64/www/Upload/';
    const  BASE_CHEMIN_DOCUWARE = 'C:/DOCUWARE/';

    private function copyFile(string $sourcePath, string $destinationPath): void
    {
        if (!file_exists($sourcePath)) {
            throw new \Exception("Le fichier source n'existe pas : $sourcePath");
        }

        if (!copy($sourcePath, $destinationPath)) {
            throw new \Exception("Impossible de copier le fichier : $sourcePath vers $destinationPath");
        }

        echo "Fichier copié avec succès : $destinationPath\n";
    }


    /**
     * Copie le PDF generer dans l'upload 
     */
    public function copyInterneToDOXCUWARE($NumDom, $codeAg_serv)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE .'ORDRE_DE_MISSION/' . $NumDom . '_' . $codeAg_serv . '.pdf';
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . strtolower(substr($NumDom, 0, 3)) . '/' . $NumDom . '_'  . $codeAg_serv . '.pdf';
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDw($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'ORDRE_DE_MISSION/oRValidation_' .$numeroOR.'_'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'vor/oRValidation_' .$numeroOR.'_'.$numeroVersion . '.pdf';
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }  

    public function copyToDwFactureSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . '/ORDRE_DE_MISSION/factureValidation_' .$numeroOR.'_'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'vfac/factureValidation_' .$numeroOR.'_'.$numeroVersion . '.pdf';
       copy($cheminDestinationLocal, $cheminFichierDistant);
    } 
        
    public function copyToDwRiSoumis($numeroVersion, $numeroOR)
    {
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'RAPPORT_INTERVENTION/RI_' .$numeroOR.'-'. $numeroVersion. '.pdf';
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'vri/RI_' .$numeroOR.'-'.$numeroVersion . '.pdf'; // avec tiret 6
        copy($cheminDestinationLocal, $cheminFichierDistant);
    } 

    public function copyToDWCdeSoumis($fileName){
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE .'ORDRE_DE_MISSION/' .$fileName;
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'cde/' .$fileName;
        if (copy($cheminDestinationLocal, $cheminFichierDistant)) {
            echo "okey";
        } else {
            echo "sorry";
        }
    }

    public function copyToDWDevisSoumis($fileName){
        $cheminFichierDistant = self::BASE_CHEMIN_DOCUWARE . 'ORDRE_DE_MISSION/' .$fileName;
        $cheminDestinationLocal = self::BASE_CHEMIN_DU_FICHIER . 'dit/dev/' .$fileName;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }       
}