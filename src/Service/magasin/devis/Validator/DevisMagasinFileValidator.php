<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Service\validation\ValidationServiceBase;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;

/**
 * Validateur spécialisé pour les fichiers des devis magasin
 * 
 * Ce service gère exclusivement la validation des fichiers soumis
 * pour les devis magasin, incluant le format et la cohérence des noms.
 */
class DevisMagasinFileValidator extends ValidationServiceBase
{
    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du validateur de fichiers
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(
        HistoriqueOperationDevisMagasinService $historiqueService,
        string $expectedNumeroDevis
    ) {
        $this->historiqueService = $historiqueService;
        $this->expectedNumeroDevis = $expectedNumeroDevis;
    }

    /**
     * Valide le fichier soumis pour un devis magasin
     * 
     * Cette méthode vérifie :
     * - Si un fichier a été soumis
     * - Si le nom du fichier correspond au format attendu (DEVIS MAGASIN_XXX_XXX_XXX.pdf)
     * - Si le numéro de devis dans le nom du fichier correspond au numéro attendu
     * 
     * @param FormInterface $form Le formulaire contenant le fichier à valider
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form): bool
    {
        // Vérifie si un fichier a été soumis
        if (!$this->isFileSubmitted($form, DevisMagasinValidationConfig::FILE_FIELD_NAME)) {
            $this->sendNotification(
                DevisMagasinValidationConfig::ERROR_MESSAGES['no_file_submitted'],
                '',
                false
            );
            return false;
        }

        $file = $form->get(DevisMagasinValidationConfig::FILE_FIELD_NAME)->getData();
        $fileName = $file->getClientOriginalName();

        // Vérifie si le nom du fichier correspond au pattern attendu
        if (!$this->validateFileFormat($fileName)) {
            return false;
        }

        // Vérifie si le numéro de devis dans le nom du fichier correspond au numéro attendu
        if (!$this->validateFileNumber($fileName)) {
            return false;
        }

        return true;
    }

    /**
     * Valide le format du nom de fichier
     * 
     * @param string $fileName Le nom du fichier à valider
     * @return bool true si le format est valide, false sinon
     */
    private function validateFileFormat(string $fileName): bool
    {
        if (!$this->matchPattern($fileName, DevisMagasinValidationConfig::FILENAME_PATTERN)) {
            $message = sprintf(
                DevisMagasinValidationConfig::ERROR_MESSAGES['invalid_filename_format'],
                $fileName
            );
            $this->sendNotification($message, '', false);
            return false;
        }

        return true;
    }

    /**
     * Valide que le numéro de devis dans le nom de fichier correspond au numéro attendu
     * 
     * @param string $fileName Le nom du fichier à valider
     * @return bool true si le numéro correspond, false sinon
     */
    private function validateFileNumber(string $fileName): bool
    {
        if (!$this->matchNumberAfterUnderscore($fileName, $this->expectedNumeroDevis)) {
            $message = sprintf(
                DevisMagasinValidationConfig::ERROR_MESSAGES['filename_number_mismatch'],
                $fileName,
                $this->expectedNumeroDevis
            );
            $this->sendNotification($message, $this->expectedNumeroDevis, false);
            return false;
        }

        return true;
    }

    /**
     * Envoie une notification via le service d'historique
     * 
     * @param string $message Le message à envoyer
     * @param string $numeroDevis Le numéro de devis concerné
     * @param bool $success Indique si l'opération a réussi
     */
    private function sendNotification(string $message, string $numeroDevis, bool $success): void
    {
        $this->historiqueService->sendNotificationSoumission(
            $message,
            $numeroDevis,
            DevisMagasinValidationConfig::REDIRECT_ROUTE,
            $success
        );
    }
}
