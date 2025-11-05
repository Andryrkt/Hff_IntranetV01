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
            ],
            'sauter_premier_index' => false // Ne pas sauter le premier index
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
     */
    public function generateBonCommandeName(
        UploadedFile $file,
        string $numDevis,
        int $numeroVersion,
        int $index = 1
    ): string {
        return $this->generateFileName($file, [
            'format' => 'bon_commande_{numDevis}-{numeroVersion}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
            ],
            'sauter_premier_index' => false
        ], $index);
    }

    /**
     * Génère un nom pour le page de garde du bon de commande
     */
    public function generatePageGardeBonCommandeName(
        string $numDevis,
        int $numeroVersion
    ): string {
        return "bon_commande_{$numDevis}-{$numeroVersion}.pdf";
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
