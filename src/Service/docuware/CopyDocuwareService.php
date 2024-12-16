<?php

namespace App\Service\docuware;

class CopyDocuwareService
{
    public function copyCsvToDw($fileName, $filePath)
    {
        $cheminFichierDepart = 'C:/DOCUWARE/ORDRE_DE_MISSION/'.$fileName;
        $cheminDestination = $filePath;

        copy($cheminDestination, $cheminFichierDepart);
    }  
}