<?php

namespace App\Service\docuware;

class CopyDocuwareService
{
    private $docuwarePath;

    public function __construct(string $docuwareFtpPath)
    {
        // Utiliser directement la variable d'environnement pour éviter les problèmes de résolution
        $envPath = $_ENV['BASE_PATH_DOCUWARE'] ?? 'C:/DOCUWARE';
        $this->docuwarePath = rtrim($envPath, '/') . '/';
    }

    public function copyCsvToDw(string $fileName, string $sourcePath): void
    {
        $destinationPath = $this->docuwarePath . $fileName;

        // Ajout d'une gestion d'erreur
        if (!file_exists($sourcePath)) {
            throw new \RuntimeException("Le fichier source '$sourcePath' n'existe pas.");
        }

        $result = @copy($sourcePath, $destinationPath);

        if ($result === false) {
            $error = error_get_last();
            throw new \RuntimeException("Impossible de copier le fichier vers Docuware : " . ($error['message'] ?? 'erreur inconnue'));
        }
    }
}
