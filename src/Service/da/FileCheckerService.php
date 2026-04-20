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

    public function checkBapFileExists(?string $numeroDa, ?string $numeroCde = ''): bool
    {
        if (empty($numeroDa) || empty($numeroCde)) {
            return false;
        }

        $filePath = $this->projectDir . "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        return $this->filesystem->exists($filePath);
    }

    public function getBapFilePath(?string $numeroDa, string $numeroCde): ?string
    {
        if (empty($numeroDa) || empty($numeroCde)) {
            return null;
        }
        $relativePath = "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $relativePath;
        }

        return null;
    }

    public function getBapFullPath(?string $numeroDa, string $numeroCde): ?string
    {
        if (empty($numeroDa) || empty($numeroCde)) {
            return null;
        }
        // $relativePath = "/da/$numeroDa/BAP_{$numeroDa}_{$numeroCde}.pdf";
        $relativePath = "/da/$numeroDa/BAP-$numeroCde#$numeroDa.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }

    public function getDdpFullPath(?string $numeroDdp): ?string
    {

        $relativePath = "/ddp/$numeroDdp/$numeroDdp.pdf";
        $fullPath = $this->projectDir .  $relativePath;

        if ($this->filesystem->exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }
}
