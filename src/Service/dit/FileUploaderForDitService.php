<?php

namespace App\Service\dit;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderForDitService
{
    private string $basePath;
    public const FILE_TYPE = [
        "OBSERVATION" => "observation_pj",
    ];

    public function __construct()
    {
        $this->basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/');
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
     * Upload un fichier pour DIT
     *
     * @param UploadedFile $file      fichier à uploader
     * @param string       $numeroDit numéro de la Demande d'Intervention
     * @param string       $fileType  type du fichier joint (PJ observation)
     * @param int          $i         incrémentation pour les fichiers multiples
     *
     * @return string le nom du fichier final
     */
    public function uploadDitFile(UploadedFile $file, string $numeroDit, string $fileType, int $i = 0): string
    {
        $fileName = sprintf(
            '%s_%s.%s',
            $fileType,
            md5(date("Y|m|d|H|i|s") . $i),
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        );

        $destination = "{$this->basePath}/dit/$numeroDit/";

        $this->moveFile($file, $fileName, $destination);

        return $fileName;
    }

    /**
     * Upload multiple Dit Files
     *
     * @param UploadedFile[] $files     les fichiers à uploader
     * @param string         $numeroDit numéro de la Demande d'Intervention
     * @param string         $fileType  type du fichier joint (PJ observation)
     *
     * @return array tableau des noms de fichiers finals
     */
    public function uploadMultipleDitFiles(?array $files, string $numeroDit, string $fileType): array
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1;
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->uploadDitFile($file, $numeroDit, $fileType, $i);
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++;
                $fileNames[] = $fileName;
            }
        }
        return $fileNames;
    }
}
