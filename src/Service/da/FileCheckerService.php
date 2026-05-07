<?php

namespace App\Service\da;

use Symfony\Component\Filesystem\Filesystem;

class FileCheckerService
{
    private $projectDir;
    private $filesystem;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->filesystem = new Filesystem();
    }

    public function checkBapFileExists(?string $numeroDdp): bool
    {
        if (empty($numeroDdp)) {
            return false;
        }

        $filePath = $this->projectDir . "/ddp/$numeroDdp/$numeroDdp.pdf";
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
