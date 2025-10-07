<?php

namespace App\Service\magasin\devis\Fichier;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\fichier\AbstractFileNameGeneratorService;

class DevisMagasinGenererNameFileService extends AbstractFileNameGeneratorService
{
    /**
     * Génère un nom pour votre cas spécifique de vérification de prix
     */
    public function generateVerificationPrixName(
        UploadedFile $file,
        string $numDevis,
        int $numeroVersion,
        string $suffix,
        string $mail,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'verificationprix_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix,
                'mail' => $mail
            ]
        ], $index);
    }

    /**
     * Génère un nom pour votre cas spécifique de vérification de prix
     */
    public function generateValidationDevisName(
        UploadedFile $file,
        string $numDevis,
        int $numeroVersion,
        string $suffix,
        string $mail,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'validationdevis_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix,
                'mail' => $mail
            ]
        ], $index);
    }

    /**
     * Génère un nom pour le bon de commande
     * TODO: mbola vao ho avy
     */
    public function generateBonCommandeName(
        UploadedFile $file,
        string $numDevis,
        string $fournisseur,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'bon_commande_{numDevis}_{fournisseur}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'fournisseur' => $this->sanitizeFileName($fournisseur),
            ]
        ], $index);
    }

    /**
     * Génère un nom pour la facture
     * TODO: mbola vao ho avy
     */
    public function generateFactureName(
        UploadedFile $file,
        string $numDevis,
        string $typeFacture,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'facture_{typeFacture}_{numDevis}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'typeFacture' => $typeFacture,
            ]
        ], $index);
    }

    /**
     * Nettoie le nom de fichier pour éviter les caractères spéciaux
     */
    private function sanitizeFileName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    }
}
