<?php

namespace App\Service\da;

use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderForDAService
{
    private string $basePath;
    public const FILE_TYPE = [
        "DEVIS"           => "devis_pj",
        "FICHE_TECHNIQUE" => "fiche_technique",
        "OBSERVATION"     => "observation_pj",
    ];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Déplace un fichier uploadé vers une destination
     */
    private function moveFile(UploadedFile $file, string $fileName, string $destination): void
    {
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }
    }

    /**
     * Upload un fichier pour DA
     * 
     * @param UploadedFile $file               fichier à uploader
     * @param string       $numeroDemandeAppro numéro de la Demande Appro
     * @param string       $fileType           type du fichier joint (devis, fiche technique, PJ observation)
     * @param int          $i                  incrémentation pour les fichiers multiples
     * 
     * @return string le nom du fichier final
     */
    public function uploadDaFile(UploadedFile $file, string $numeroDemandeAppro, string $fileType, int $i = 0): string
    {
        $fileName = sprintf(
            '%s_%s.%s',
            $fileType,
            md5(date("Y|m|d|H|i|s") . $i),
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        );

        $destination = "{$this->basePath}/da/$numeroDemandeAppro/";

        $this->moveFile($file, $fileName, $destination);

        return $fileName;
    }

    /**
     * Upload multiple Da Files
     * 
     * @param UploadedFile[] $files              les fichiers à uploader
     * @param string         $numeroDemandeAppro numéro de la Demande Appro
     * @param string         $fileType           type du fichier joint (devis, fiche technique, PJ observation)
     * 
     * @return array tableau des noms de fichiers finals
     */
    public function uploadMultipleDaFiles(?array $files, string $numeroDemandeAppro, string $fileType): array
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->uploadDaFile($file, $numeroDemandeAppro, $fileType, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        return $fileNames;
    }
}
