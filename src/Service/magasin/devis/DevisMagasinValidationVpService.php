<?php

namespace App\Service\magasin\devis;

use Symfony\Component\Form\FormInterface;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * Service de validation pour les devis magasin - Validation de Prix (VP)
 * 
 * Ce service gère toutes les validations nécessaires pour la validation de prix des devis magasin,
 * incluant la vérification des fichiers, des statuts et des modifications de contenu.
 */
class DevisMagasinValidationVpService extends ValidationServiceBase
{
    private const FILE_FIELD_NAME = 'pieceJoint01';
    private const FILENAME_PATTERN = '/^(DEVIS MAGASIN|CONTROLE DEVIS)_(\d+)_(\d+)_(\d+)\\.pdf$/';

    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du service de validation de prix des devis magasin
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
     * Vérifie si le numéro de devis est manquant lors de la validation de prix
     * 
     * @param string|null $numeroDevis Le numéro de devis à vérifier
     * @return bool true si le numéro de devis est présent, false sinon
     */
    public function checkMissingIdentifier(?string $numeroDevis): bool
    {
        if ($this->isIdentifierMissing($numeroDevis)) {
            $message = "Le numero de devis est obligatoire pour la soumission.";
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false; // Validation failed
        }
        return true; // Validation passed
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
        if (!$this->isFileSubmitted($form, self::FILE_FIELD_NAME)) {
            $message = "Aucun fichier n'a été soumis.";
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false;
        }

        $file = $form->get(self::FILE_FIELD_NAME)->getData();
        $fileName = $file->getClientOriginalName();

        // Vérifie si le nom du fichier correspond au pattern attendu (S'assurer que c'est bien un devis qui soit soumis)
        if (!$this->matchPattern($fileName, self::FILENAME_PATTERN)) {
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

    /**
     * Bloqué si le devis est en cours de vérification de prix
     * 
     * Cette méthode vérifie si le devis est dans un statut en cours de vérification de prix 
     * (ex: "prix à confirmer", "Soumis à validation")
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmission(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $blockingStatuses = [
            'Prix à confirmer',
            'Soumis à validation'
        ];

        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Soumission bloquée, une vérification de prix est déjà en cours sur ce devis";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }

    /**
     * Vérifie si le statut du devis bloque la soumission pour la validation de devis (VD)
     * 
     * Cette méthode empêche l'utilisateur de soumettre un devis à validation de prix
     * si le prix a déjà été vérifié ou si le devis est dans un autre statut bloquant
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionForVd(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $blockingStatuses = [
            'Prix validé magasin',
            'Prix refusé magasin',
            'A valider chef d’agence'
        ];

        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Le prix a été déjà vérifié ... Veuillez soumettre le devis à validation";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }

    /**
     * Vérifie si le nombre de lignes du devis a changé pour la validation de prix
     * 
     * Cette méthode compare le nombre de lignes actuel avec le nombre de lignes précédent
     * pour détecter les modifications qui nécessitent une validation de devis avant
     * la validation de prix
     * 
     * @param LatestSumOfLinesRepositoryInterface $repository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @return bool true si le nombre de lignes est inchangé (bloquant), false sinon
     */
    public function estSommeDeLigneInChanger(
        LatestSumOfLinesRepositoryInterface $repository,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($numeroDevis);

        if ($oldSumOfLines === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return false;
        }

        if ($this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)) {
            $message = "soumission bloquée (doit passer par validation devis)";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return true; // Is blocking
        }

        return false; // Is not blocking
    }
}
