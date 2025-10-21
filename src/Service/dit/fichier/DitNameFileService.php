<?php

namespace App\Service\dit\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\fichier\AbstractFileNameGeneratorService;

class DitNameFileService extends AbstractFileNameGeneratorService
{
    /**
     * Génère un nom pour votre cas spécifique de demande d'intervention
     */
    public function generateDitName(
        UploadedFile $file,
        string $numDit,
        string $agServEmetteur,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => '{numDit}_{agServEmetteur}.{extension}',
            'variables' => [
                'numDit' => $numDit,
                'agServEmetteur' => $agServEmetteur
            ],
            'sauter_premier_index' => false // Ne pas sauter le premier index
        ], $index);
    }
}
