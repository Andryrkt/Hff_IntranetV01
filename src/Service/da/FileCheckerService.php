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

    public function checkBapFileExists(string $numeroDa, ?string $numeroCde = ''): bool
    {

        $filePath = $this->projectDir . "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        return $this->filesystem->exists($filePath);
    }

    public function getBapFilePath(string $numeroDa, string $numeroCde): ?string
    {
        $relativePath = "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $relativePath;
        }

        return null;
    }

    public function getBapFullPath(string $numeroDa, string $numeroCde): ?string
    {
        $relativePath = "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }
}
