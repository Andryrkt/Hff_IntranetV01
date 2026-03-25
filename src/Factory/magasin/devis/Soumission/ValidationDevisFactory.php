<?php

namespace App\Factory\magasin\devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\SoumissionDto;
use App\Model\magasin\devis\Soumission\SoumissionModel;
use App\Service\autres\VersionService;
use DirectoryIterator;

class ValidationDevisFactory
{
    public static function create(?string $typeSoumission = null, ?string $numeroDevis = null, $codeSociete = null)
    {
        $dto = new SoumissionDto();
        $dto->typeSoumission = $typeSoumission;
        $dto->numeroDevis = $numeroDevis;
        $dto->codeSociete = $codeSociete;
        $dto->remoteUrlCourt = self::getLastEditedDevis($numeroDevis)["court"];

        return $dto;
    }

    public static function CreateBeforeSoumission(SoumissionDto $dto, $userName, $userMail): SoumissionDto
    {
        $devisNegModel = new SoumissionModel();

        $dto->suffix = $devisNegModel->constructeurPieceMagasin($dto->numeroDevis);
        $dto->numeroVersion = VersionService::autoIncrement($devisNegModel->getNumeroVersion($dto->numeroDevis));
        $dto->userName = $userName;
        $dto->userMail = $userMail;
        $dto->dateCreation = date('Y-m-d H:i:s');

        return $dto;
    }

    private static function getLastEditedDevis(string $numeroDevis): array
    {
        $filePath = '';
        $destination = '';
        $dossier = "\\\\192.168.0.15\\hff_pdf\\VALIDATION VENTE NEGOCE\\";   // dossier contenant les fichiers
        $dernierFichier = null;
        $derniereDate = 0;

        $it = new DirectoryIterator($dossier);

        foreach ($it as $fichier) {
            if ($fichier->isFile()) {
                $nom = $fichier->getFilename();

                if (preg_match('/DEVIS MAGASIN_' . $numeroDevis . '_(\d{14})_\d+\.pdf$/', $nom, $matches)) {
                    $timestamp = $matches[1];

                    if ($timestamp > $derniereDate) {
                        $derniereDate = $timestamp;
                        $dernierFichier = $nom;
                    }
                }
            }
        }

        // Copier le fichier en local si existant
        if ($dernierFichier) {
            $remoteUrl = $dossier . $dernierFichier; // chemin du fichier dans le dossier partagé 192.168.0.15
            $devisPath = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/' . $numeroDevis . '/'; // chemin complet du dossier local
            $destination = $devisPath . $dernierFichier; // chemin complet du fichier local
            if (!is_dir($devisPath)) mkdir($devisPath, 0777, true); // creation du dossier local si n'existe pas
            if (!file_exists($destination)) copy($remoteUrl, $destination); // copie du fichier local si n'existe pas
            $filePath =  $_ENV['BASE_PATH_FICHIER_COURT'] . '/magasin/devis/' . "$numeroDevis/$dernierFichier"; // chemin court du fichier local
        }

        return [
            "court" => $filePath,
            "long"  => $destination
        ];
    }
}
