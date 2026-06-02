<?php

namespace App\Service\da;

use Symfony\Component\Filesystem\Filesystem;

class FileCheckerService
{
    private string $projectDir;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->projectDir = $_ENV['BASE_PATH_FICHIER'];
        $this->filesystem = new Filesystem();
    }

    public function checkBapFileExists(?string $filePath): bool
    {
        if (!$filePath) return false;

        return $this->filesystem->exists($filePath);
    }

    public function getBapFilePath(?string $numeroDdp): ?string
    {
        if (empty($numeroDdp)) {
            return null;
        }
        $relativePath = "/ddp/$numeroDdp/$numeroDdp.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $relativePath;
        }

        return null;
    }


    public function getFullPath(?string $numeroDdp): ?string
    {
        $relativePath = "/ddp/$numeroDdp/$numeroDdp.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }
}
