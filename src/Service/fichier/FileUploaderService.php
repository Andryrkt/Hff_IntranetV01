<?php

namespace App\Service\fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderService
{
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, string $prefix = ''): string
    {
        $fileName = $prefix . $this->generateUniqueFileName() . '.' . $file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Une erreur est survenue lors du téléchargement du fichier.");
        }

        return $fileName;
    }

    private function generateUniqueFileName(): string
    {
        return uniqid();
    }



    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
