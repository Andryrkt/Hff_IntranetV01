<?php

namespace App\Service\pol\ddd\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\fichier\AbstractFileNameGeneratorService;

class DddNameFileService extends AbstractFileNameGeneratorService
{
    /**
     * Génère un nom pour les fichiers joints d'une demande de diagnostic pneu.
     *
     * @param UploadedFile $file         Fichier uploadé
     * @param string       $numDemande   Numéro de la demande (ex: DDD-2026-001)
     * @param string       $identifiant  Identifiant supplémentaire (ex: code atelier, code société)
     * @param int          $index        Index pour les fichiers multiples (1,2,3...)
     * @return string
     */
    public function generateDddFileName(
        UploadedFile $file,
        string $numDemande,
        string $identifiant,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => '{numDemande}_{identifiant}_{index}.{extension}',
            'variables' => [
                'numDemande'   => $numDemande,
                'identifiant'  => $identifiant,
                'index'        => $index,
            ],
            'sauter_premier_index' => false, // L'index commence à 1
        ], $index);
    }

    /**
     * Génère un nom pour le fichier principal (PDF généré) d'une demande de diagnostic pneu.
     *
     * @param string $numDemande   Numéro de la demande
     * @param string $identifiant  Identifiant (ex: atelier, code société)
     * @param bool   $withSuffix   Si true, ajoute "#DDD" avant l'extension
     * @return string
     */
    public function generateDddNamePrincipal(
        string $numDemande,
        string $identifiant,
        bool $withSuffix = false
    ): string {
        $suffix = $withSuffix ? '#DDD' : '';
        return $numDemande . '_' . $identifiant . $suffix . '.pdf';
    }
}
