<?php

namespace App\Service\fichier;

use App\Service\FusionPdf;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class TraitementDeFichier
{

    private FusionPdf $fusionPdf;

    public function __construct()
    {
        $this->fusionPdf = new FusionPdf();
    }

    public function upload(UploadedFile $file, string $cheminDeBase, string $fileName): void
    {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException("Le fichier fourni n'est pas une instance de UploadedFile.");
        }

        $tempPath = $file->getPathname();

        if (!file_exists($tempPath)) {
            throw new \RuntimeException(
                "Le fichier temporaire a été supprimé. " .
                    "Temp path: " . $tempPath .
                    ", Size: " . $file->getSize() .
                    ", Error: " . $file->getError()
            );
        }

        if (!is_uploaded_file($tempPath)) {
            throw new \RuntimeException("Ce n'est pas un fichier uploadé valide");
        }

        try {
            // Debug : chemin réel du fichier temporaire
            // dd($file->getRealPath());

            $file->move($cheminDeBase, $fileName);
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors du téléchargement du fichier : " . $e->getMessage());
        }
    }


    /**
     * Methode qui permet de crée un tableau qui contient les chemis de fichier à fusionner
     * il permet aussi de specifier le position du page principal
     * position 0 si le fichier principal doit etre en premier page
     * position 1 si le fichier principal doit etre en deuxieme page
     *  ex : ['fichier1.pdf', 'fichier2.pdf', 'fichier3.pdf', 'fichier4.pdf']
     *
     * @param array $uploadedFiles // tableau des chemin de fichier à fusionner
     * @param string $mainFilePathName // chemin du fichier principal (ilay atsofoka)
     * @param integer $position // position du fichier principal
     * @return array
     */
    public function insertFileAtPosition(array $uploadedFiles, string $mainFilePathName, int $position = 0): array
    {
        // S'assurer que la position est valide
        $position = max(0, min($position, count($uploadedFiles)));

        // Insérer le fichier principal à la position spécifiée
        array_splice($uploadedFiles, $position, 0, [$mainFilePathName]);

        return $uploadedFiles;
    }

    public function fusionFichers(array $uploadedFiles, string $nomFichierFusioner)
    {
        $this->fusionPdf->mergePdfs($uploadedFiles, $nomFichierFusioner);
    }
}
