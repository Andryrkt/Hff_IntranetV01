<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Service\validation\ValidationServiceBase;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Traits\Validator\ValidatorNotificationTrait;

/**
 * Validateur spécialisé pour les fichiers des devis magasin VP
 * 
 * Ce service gère exclusivement la validation des fichiers
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpFileValidator extends ValidationServiceBase
{
    use ValidatorNotificationTrait;

    private string $expectedNumeroDevis;
    private string $remoteUrl;

    /**
     * Constructeur du validateur de fichiers VP
     * 
     * 
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(string $expectedNumeroDevis, string $remoteUrl)
    {
        $this->expectedNumeroDevis = $expectedNumeroDevis;
        $this->remoteUrl = $remoteUrl;
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
        if (!$this->remoteUrl && !$this->isFileSubmitted($form, DevisMagasinValidationConfig::FILE_FIELD_NAME)) {
            $message = "Aucun fichier n'a été soumis.";
            $this->sendNotificationDevisMagasin($message, '', false);
            return false;
        }

        $file = $form->get(DevisMagasinValidationConfig::FILE_FIELD_NAME)->getData();

        if ($this->remoteUrl) {
            $parts = explode("/", $this->remoteUrl);
            $fileName = end($parts); // récupère le dernier élément du tableau
        } else {
            $fileName = $file->getClientOriginalName();
        }

        // Vérifie si le nom du fichier correspond au pattern attendu (S'assurer que c'est bien un devis qui soit soumis)
        if (!$this->matchPattern($fileName, DevisMagasinValidationConfig::FILENAME_PATTERN)) {
            $message = "Le nom du fichier soumis n'est pas conforme au format attendu. Reçu: " . $fileName;
            $this->sendNotificationDevisMagasin($message, '', false);
            return false;
        }

        // Vérifie si le numéro de devis dans le nom du fichier correspond au numéro de devis attendu (S'assurer que le devis envoyé corresponde à la ligne de devis utilisé pour la soumission dans l'intranet)
        if (!$this->matchNumberAfterUnderscore($fileName, $this->expectedNumeroDevis)) {
            $message = "Le numéro de devis dans le nom du fichier ($fileName) ne correspond pas au devis du formulaire ( $this->expectedNumeroDevis )";
            $this->sendNotificationDevisMagasin($message, $this->expectedNumeroDevis, false);
            return false;
        }

        return true;
    }
}
