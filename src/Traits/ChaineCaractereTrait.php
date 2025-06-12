<?php

namespace App\Traits;

trait ChaineCaractereTrait
{
    /**
     * effacer l'extension de chaque element du tableau files
     *
     * @param array $files
     * @return array
     */
    private function removePdfExtension(array $files): array {
        return array_map(function($file) {
            return preg_replace('/\.pdf$/i', '', $file);
        }, $files);
    }
}
