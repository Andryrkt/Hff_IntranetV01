<?php

namespace App\Service\fichier;


class TraitementDeFichier
{
    public function upload($file, $cheminDeBase,$fileName): void
    {
        try {
            $file->move($cheminDeBase, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Une erreur est survenue lors du téléchargement du fichier.");
        }
    }
}