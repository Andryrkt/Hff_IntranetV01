<?php


namespace App\Service\dit\ors;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\fichier\AbstractFileNameGeneratorService;

class OrGeneratorNameService extends AbstractFileNameGeneratorService
{

    /**
     * Génère un nom pour les fichiers
     */
    public function generateNameFile(
        UploadedFile $file,
        string $numOr,
        int $numeroVersion,
        string $suffix,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'oRValidation_{numOr}-{numeroVersion}#{suffix}.{extension}',
            'variables' => [
                'numOr' => $numOr,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix
            ],
            'sauter_premier_index' => false // Ne pas sauter le premier index
        ], $index);
    }
    /**
     * Gerer un nom pour la page de garde et le fichier fusionner
     */
    public function generateNamePrincipal(
        string $numOr,
        int $numeroVersion,
        string $suffix
    ) {
        return "oRValidation_$numOr-$numeroVersion#$suffix.pdf";
    }
}
