<?php

namespace App\Service\magasin\devis\Validation;

use App\Model\magasin\devis\Soumission\SoumissionModel;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Traits\Validator\ValidatorNotificationTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ValidationSoumissionVerificationPrix
{
    use ValidatorNotificationTrait;

    private const FILE_FIELD_NAME = 'pieceJoint01';
    private const FILENAME_PATTERN = '/^(DEVIS MAGASIN|CONTROLE DEVIS)_(\d+)_(\d+)_(\d+)\\.pdf$/';

    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;

    public function __construct()
    {
        global $container;
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
    }

    public function validateSoumissionVerificationPrixAvantAffichageFormulaire($numeroDevis, $codeSociete): bool
    {
        $soumissionModel = new SoumissionModel();
        $ancienDevis = $soumissionModel->getInfoDevisForValidate($numeroDevis, $codeSociete);
        $nouveauDevis = $soumissionModel->getInfoDevis($numeroDevis, $codeSociete);
        // Bloqué si :
        // le numéro de devis n'existe pas ou est vide
        if (empty($numeroDevis)) {
            $message = "Le numero de devis est obligatoire pour la soumission.";
            $this->sendNotificationDevisMagasin($message, '', 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // le statut devis est Prix à confirmer
        if ($ancienDevis && $ancienDevis['statut'] === 'Prix à confirmer') {
            $message = 'Une confirmation de prix pour ce devis est déjà en cours au magasin.';
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // le statut devis est Prix validé - devis à soumettre (si agence) et la somme des lignes du devis inchangé et montant devis inchangé
        if (
            $ancienDevis && $ancienDevis['statut'] === 'Prix validé - devis à soumettre (si agence)'
            && $ancienDevis['somme_numero_lignes'] === $nouveauDevis['somme_numero_lignes']
            && $ancienDevis['montant_devis'] === $nouveauDevis['montant_devis']
        ) {
            $message = "Les prix ont déjà été validés par le parts manager,. Veuillez faire valider le devis au chef d'agence";
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // le statut devis est Prix modifié - devis à soumettre (si agence) et lasomme des lignes du devis inchangée mais le montant devis change
        if (
            $ancienDevis && $ancienDevis['statut'] === 'Prix modifié - devis à soumettre (si agence)'
            && $ancienDevis['somme_numero_lignes'] === $nouveauDevis['somme_numero_lignes']
            && $ancienDevis['montant_devis'] !== $nouveauDevis['montant_devis']
        ) {
            $message =  "Les prix ont déjà été validés par le parts manager,. Veuillez faire valider le devis au chef d'agence";
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // le statut devis est Validé - à envoyer au client et la somme des lignes du devis change et montant devis change
        if (
            $ancienDevis && $ancienDevis['statut'] === 'Validé - à envoyer au client'
            && ($ancienDevis['somme_numero_lignes'] !== $nouveauDevis['somme_numero_lignes']
                && $ancienDevis['montant_devis'] !== $nouveauDevis['montant_devis'])
        ) {
            $message =  "Le montant du devis validé ne correspond pas au montant du devis dans IPS. Veuillez refaire valider le devis.";
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // le statut devis est A valider chef d'agence
        if ($ancienDevis && $ancienDevis['statut'] === 'A valider chef d\'agence') {
            $message =  "Un devis est en cours de validation chez le chef d'agence";
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        // Le statut devis est Validé - à envoyer au client et la somme des lignes du devis inchangée
        if (
            $ancienDevis && $ancienDevis['statut'] === 'Validé - à envoyer au client'
            && $ancienDevis['somme_numero_lignes'] === $nouveauDevis['somme_numero_lignes']
        ) {
            $message =  "Le devis a déjà été validé. Veuillez l'envoyer au client";
            $this->sendNotificationDevisMagasin($message, $numeroDevis, 'liste_devis_neg', false);
            return true; // Validation failed
        }

        return false;
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
     * @param string|null $remoteUrl L'URL distante du fichier soumis (optionnel, utilisé si le fichier est soumis via une URL)
     * @param string|null $expectedNumeroDevis Le numéro de devis attendu pour la validation
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form, ?string $remoteUrl = null, $expectedNumeroDevis = null): bool
    {
        // Vérifie si un fichier a été soumis
        if (!$remoteUrl && !$this->isFileSubmitted($form, self::FILE_FIELD_NAME)) {
            $message = "Aucun fichier n'a été soumis.";
            $this->sendNotificationDevisMagasin($message, '', 'liste_devis_neg', false);
            return false;
        }

        $file = $form->get(self::FILE_FIELD_NAME)->getData();

        if ($remoteUrl) {
            $parts = explode("/", $remoteUrl);
            $fileName = end($parts); // récupère le dernier élément du tableau
        } else {
            $fileName = $file->getClientOriginalName();
        }

        // Vérifie si le nom du fichier correspond au pattern attendu (S'assurer que c'est bien un devis qui soit soumis)
        if (!$this->matchPattern($fileName, self::FILENAME_PATTERN)) {
            $message = "Le nom du fichier soumis n'est pas conforme au format attendu. Reçu: " . $fileName;
            $this->sendNotificationDevisMagasin($message, '', 'liste_devis_neg', false);
            return false;
        }

        // Vérifie si le numéro de devis dans le nom du fichier correspond au numéro de devis attendu (S'assurer que le devis envoyé corresponde à la ligne de devis utilisé pour la soumission dans l'intranet)
        if (!$this->matchNumberAfterUnderscore($fileName, $expectedNumeroDevis)) {
            $message = "Le numéro de devis dans le nom du fichier ($fileName) ne correspond pas au devis du formulaire ( $expectedNumeroDevis )";
            $this->sendNotificationDevisMagasin($message, '', 'liste_devis_neg', false);
            return false;
        }

        return true;
    }

    /**
     * Vérifie si un fichier a été soumis dans un champ de formulaire donné
     * 
     * @param FormInterface $form Le formulaire à vérifier
     * @param string $fieldName Le nom du champ de fichier à vérifier
     * @return bool true si un fichier a été soumis, false sinon
     */
    protected function isFileSubmitted(FormInterface $form, string $fieldName): bool
    {
        if (!$form->has($fieldName)) {
            return false;
        }

        $file = $form->get($fieldName)->getData();

        return $file instanceof UploadedFile;
    }

    /**
     * Vérifie si une chaîne de caractères correspond à un pattern regex
     * 
     * @param string|null $subject La chaîne de caractères à tester
     * @param string $pattern Le pattern regex à utiliser pour la correspondance
     * @return bool true si la chaîne correspond au pattern, false sinon
     */
    protected function matchPattern(?string $subject, string $pattern): bool
    {
        if ($subject === null) {
            return false;
        }
        return preg_match($pattern, $subject) === 1;
    }

    /**
     * Extrait un numéro après un underscore (_) dans une chaîne et le compare à une valeur attendue
     * 
     * Cette méthode est utilisée pour valider que le numéro dans un nom de fichier
     * correspond au numéro attendu (ex: "DEVIS_123_456.pdf" avec expectedNumber "123")
     * 
     * @param string $subject La chaîne de caractères contenant le numéro à extraire
     * @param string $expectedNumber Le numéro attendu pour la comparaison
     * @return bool true si le numéro extrait correspond au numéro attendu, false sinon
     */
    protected function matchNumberAfterUnderscore(string $subject, string $expectedNumber): bool
    {
        // Trouve la première séquence de chiffres qui suit un underscore
        if (preg_match('/_(\d+)/', $subject, $matches)) {
            // $matches[1] contient les chiffres capturés
            $extractedNumber = $matches[1];
            return $extractedNumber === (string) $expectedNumber;
        }

        return false; // Aucun numéro trouvé après un underscore
    }
}
