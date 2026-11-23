<?php

namespace App\Service\docuware;

use App\Service\historiqueOperation\suivi\SuiviOperationService;

class CopyDocuwareService
{
    public function copyCsvToDw($fileName, $filePath)
    {
        // $cheminFichierDepart = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $fileName;
        $cheminFichierDepart = 'ftp://ftp.docuware-online.de/VhhlMDUEYTbzBI_A8C6lpRt86g-wKO2lXFKfXfSP/data/' . $fileName;
        $cheminDestination = $filePath;

        error_clear_last();
        // le wrapper FTP refuse d'écraser un fichier existant sans l'option overwrite
        $succes = copy($cheminDestination, $cheminFichierDepart, stream_context_create(['ftp' => ['overwrite' => true]]));
        // destination masquée : l'URL FTP contient le jeton d'accès, à ne pas écrire dans le suivi
        $destinationMasquee = 'DocuWare FTP/data/' . $fileName;
        $erreur = $succes ? null : str_replace($cheminFichierDepart, $destinationMasquee, error_get_last()['message'] ?? 'La copie a échoué');
        SuiviOperationService::copieDocuware($cheminDestination, $destinationMasquee, $succes, 1, $erreur);
    }
}
