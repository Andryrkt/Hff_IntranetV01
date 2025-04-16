<?php

namespace App\Service\docuware;

class CopyDocuwareService
{
    public function copyCsvToDw($fileName, $filePath)
    {
        // $cheminFichierDepart = 'C:/DOCUWARE/ORDRE_DE_MISSION/' . $fileName;
        $cheminFichierDepart = 'ftp://ftp.docuware-online.de/VhhlMDUEYTbzBI_A8C6lpRt86g-wKO2lXFKfXfSP/data/' . $fileName;
        $cheminDestination = $filePath;

        copy($cheminDestination, $cheminFichierDepart);
    }
}
