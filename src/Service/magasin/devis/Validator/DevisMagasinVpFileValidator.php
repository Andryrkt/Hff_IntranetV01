<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Service\validation\ValidationServiceBase;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;

/**
 * Validateur spécialisé pour les fichiers des devis magasin VP
 * 
 * Ce service gère exclusivement la validation des fichiers
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpFileValidator extends ValidationServiceBase
{
    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du validateur de fichiers VP
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService, string $expectedNumeroDevis)
    {
        $this->historiqueService = $historiqueService;
        $this->expectedNumeroDevis = $expectedNumeroDevis;
    }

    /**
     * Valide le fichier soumis pour la validation de prix d'un devis magasin
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
            $message = "Aucun fichier n'a été soumis.";
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false;
        }

        $file = $form->get(DevisMagasinValidationConfig::FILE_FIELD_NAME)->getData();
        $fileName = $file->getClientOriginalName();

        // Vérifie si le nom du fichier correspond au pattern attendu (S'assurer que c'est bien un devis qui soit soumis)
        if (!$this->matchPattern($fileName, DevisMagasinValidationConfig::FILENAME_PATTERN)) {
            $message = "Le nom du fichier soumis n'est pas conforme au format attendu. Reçu: " . $fileName;
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false;
        }

        // Vérifie si le numéro de devis dans le nom du fichier correspond au numéro de devis attendu (S'assurer que le devis envoyé corresponde à la ligne de devis utilisé pour la soumission dans l'intranet)
        if (!$this->matchNumberAfterUnderscore($fileName, $this->expectedNumeroDevis)) {
            $message = "Le numéro de devis dans le nom du fichier ($fileName) ne correspond pas au devis du formulaire ( $this->expectedNumeroDevis )";
            $this->historiqueService->sendNotificationSoumission($message, $this->expectedNumeroDevis, 'devis_magasin_liste', false);
            return false;
        }

        return true;
    }
}
