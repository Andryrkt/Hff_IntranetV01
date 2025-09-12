<?php

namespace App\Traits;

/**
 * Fournit des méthodes utilitaires pour récupérer des informations
 * sur un fichier (taille, nombre de pages, etc.), que ce soit en chemin local
 * ou via une URL (HTTP/FTP).
 */
trait FileUtilityTrait
{
    /**
     * Retourne la taille d'un fichier en octets.
     * 
     * @param string $path URL du fichier
     * @return int|null Taille en octets, ou null si inaccessible
     */
    protected function getFileSize(string $path): ?int
    {
        $path = trim($path, "/");
        $headers = @get_headers("http://192.168.0.28/$path", 1);
        if ($headers !== false && isset($headers['Content-Length'])) {
            return (int) $headers['Content-Length'];
        }

        return null; // Fichier introuvable ou taille inaccessible
    }

    /**
     * Retourne l'extension d'un fichier avec un point devant, ou null si aucune.
     *
     * @param string $fileName Nom du fichier ou chemin
     * @return string|null
     */
    protected function getFileExtension(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return $ext ? '.' . $ext : null;
    }

    /**
     * Retourne le nom d'un fichier sans son extension.
     *
     * @param string|null $fileName Nom du fichier ou chemin
     * @return string|null
     */
    protected function getFileNameWithoutExtension(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
