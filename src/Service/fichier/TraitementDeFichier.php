<?php

namespace App\Service\fichier;

use App\Service\FusionPdf;


class TraitementDeFichier
{

    private FusionPdf $fusionPdf;

    public function __construct()
    {
        $this->fusionPdf = new FusionPdf();
    }

    public function upload($file, $cheminDeBase,$fileName): void
    {
        try {
            $file->move($cheminDeBase, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Une erreur est survenue lors du téléchargement du fichier.");
        }
    }

    /**
     * Methode qui permet de crée un tableau qui contient les chemis de fichier à fusionner
     * il permet aussi de specifier le position du page principal
     * position 0 si le fichier principal doit etre en premier page
     * position 1 si le fichier principal doit etre en deuxieme page
     *  ex : ['fichier1.pdf', 'fichier2.pdf', 'fichier3.pdf', 'fichier4.pdf']
     *
     * @param array $uploadedFiles
     * @param string $mainFilePathName
     * @param integer $position
     * @return array
     */
    public function insertFileAtPosition(array $uploadedFiles, string $mainFilePathName, int $position = 0): array {
        // S'assurer que la position est valide
        $position = max(0, min($position, count($uploadedFiles))); 
    
        // Insérer le fichier principal à la position spécifiée
        array_splice($uploadedFiles, $position, 0, [$mainFilePathName]);
    
        return $uploadedFiles;
    }

    public function fusionFichers(array $uploadedFiles, $nomFichierFusioner)
    {
        $this->fusionPdf->mergePdfs($uploadedFiles, $nomFichierFusioner);
    }
}