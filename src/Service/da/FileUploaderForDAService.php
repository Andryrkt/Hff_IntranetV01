<?php

namespace App\Service\da;

use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderForDAService
{
    private string $basePath;
    public const FILE_TYPE = [
        "DEVIS"           => "devis_pj",
        "FICHE_TECHNIQUE" => "fiche_technique",
        "OBSERVATION"     => "observation_pj",
    ];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Déplace un fichier uploadé vers une destination
     */
    private function moveFile(UploadedFile $file, string $fileName, string $destination): void
    {
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }
    }

    /**
     * Upload un fichier pour DA
     */
    public function uploadDaFile(UploadedFile $file, string $numeroDemandeAppro, string $fileType, int $i = 0): string
    {
        $fileName = sprintf(
            '%s_%s.%s',
            $fileType,
            md5(date("Y|m|d|H|i|s") . $i),
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        );

        $destination = "{$this->basePath}/da/$numeroDemandeAppro/";

        $this->moveFile($file, $fileName, $destination);

        return $fileName;
    }

    /**
     * Upload pièce jointe pour DAL
     */
    public function uploadPJForDal(UploadedFile $file, DemandeApproL $dal, int $i): string
    {
        $fileName = sprintf(
            'PJ_%s_%s_%s.%s',
            date("YmdHis"),
            $dal->getNumeroLigne(),
            $i,
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        ); // Exemple: PJ_20250623121403_3_1.pdf

        $destination = "{$this->basePath}/da/{$dal->getNumeroDemandeAppro()}/";

        $this->moveFile($file, $fileName, $destination);

        return $fileName;
    }

    /**
     * Upload pièce jointe pour DALR
     */
    public function uploadPJForDalr(UploadedFile $file, DemandeApproLR $dalr, int $i): string
    {
        $fileName = sprintf(
            'PJ_%s_%s%s_%s.%s',
            date("YmdHis"),
            $dalr->getNumeroLigne(),
            $dalr->getNumLigneTableau(),
            $i,
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        ); // Exemple: PJ_20250623121403_34_1.pdf

        $destination = "{$this->basePath}/da/{$dalr->getNumeroDemandeAppro()}/";

        $this->moveFile($file, $fileName, $destination);

        return $fileName;
    }

    /**
     * Upload fiche technique pour DALR
     */
    public function uploadFTForDalr(UploadedFile $file, DemandeApproLR $dalr): void
    {
        $fileName = sprintf(
            'FT_%s_%s_%s.%s',
            date("YmdHis"),
            $dalr->getNumeroLigne(),
            $dalr->getNumLigneTableau(),
            strtolower($file->guessExtension() ?? $file->getClientOriginalExtension())
        ); // Exemple: FT_20250623121403_2_4.pdf

        $destination = "{$this->basePath}/da/{$dalr->getNumeroDemandeAppro()}/";

        $this->moveFile($file, $fileName, $destination);

        $dalr->setNomFicheTechnique($fileName);
    }
}
