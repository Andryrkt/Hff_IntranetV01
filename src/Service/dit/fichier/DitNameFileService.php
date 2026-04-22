<?php

namespace App\Service\dit\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\fichier\AbstractFileNameGeneratorService;

class DitNameFileService extends AbstractFileNameGeneratorService
{
    /**
     * Génère un nom pour les fichiers
     */
    public function generateDitNameFile(
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

    /**
     * Génère un nom pour le fichier principal
     *
     * @param string $numDit
     * @param string $agServEmetteur
     * @param bool   $ditPneumatique
     * @return string
     */
    public function generateDitNamePrincipal(
        string $numDit,
        string $agServEmetteur,
        bool $ditPneumatique
    ): string {
        $suffix = $ditPneumatique ? "#POL" : "";
        return $numDit . '_' . $agServEmetteur . $suffix . '.pdf';
    }
}
