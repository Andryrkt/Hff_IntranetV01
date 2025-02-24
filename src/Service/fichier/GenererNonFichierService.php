<?php

namespace App\Service\fichier;

class GenererNonFichierService
{
    /**
     * Methode qui permet de generer un nom de fichier 
     *
     * @param string $prefix
     * @param string $numeroDoc
     * @param string $numeroVersion
     * @param string $index
     * @param string $extension
     * @return string
     */
    public static function  genererNonFichier(array $options): string
    {
        $prefix = $options['prefix'] ?? '';
        $numeroDoc = $options['numeroDoc'] ?? '';
        $numeroVersion = $options['numeroVersion'] ?? '';
        $index = $option['index'] ?? '';
        $extension = $option['extension']?? '';

        return sprintf(
            '%s%s%s%s%s',
            $prefix !== ''? "{$prefix}": '',
            $numeroDoc !=='' ? "_{$numeroDoc}": '',
            $numeroVersion !== '' ? "_{$numeroVersion}" : '',
            $index !== '' ? "_0{$index}" : '',
            $extension
        );
    }

    /**
     * Methode qui generer le chemin de fichier
     *
     * @param string $path
     * @param string $nomFichier
     * @return string
     */
    public static function genererPathFichier(string $path, string $nomFichier): string
    {
        return $path.$nomFichier;
    }

}