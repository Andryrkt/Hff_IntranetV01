<?php

namespace App\Service\magasin\devis;

use Symfony\Component\Form\FormInterface;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * Service de validation pour les devis magasin
 * 
 * Ce service gère toutes les validations nécessaires pour la soumission des devis magasin,
 * incluant la vérification des fichiers, des statuts et des modifications de contenu.
 */
class DevisMagasinValidationVdService extends ValidationServiceBase
{
    private const FILE_FIELD_NAME = 'pieceJoint01';
    private const FILENAME_PATTERN = '/^(DEVIS MAGASIN|CONTROLE DEVIS)_(\d+)_(\d+)_(\d+)\\.pdf$/';

    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du service de validation des devis magasin
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
     * Vérifie si le numéro de devis est manquant lors de la soumission
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
     * Vérifie si le statut du devis bloque la soumission
     * 
     * Cette méthode vérifie si le devis est dans un statut qui empêche sa soumission
     * (ex: "A valider chef d’agence", "Soumis à validation")
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
            'A valider chef d’agence',
            'Soumis à validation'
        ];

        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Soumission bloquée, une validation est déjà en cours sur ce devis";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }
    /**
     * Vérifie si le statut du devis bloque la soumission pour la validation de prix (VP)
     * 
     * Cette méthode vérifie si le devis est dans un statut qui empêche sa soumission
     * spécifiquement pour la validation de prix (ex: "Prix à confirmer")
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionVp(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $blockingStatuses = [
            'Prix à confirmer'
        ];

        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Soumission bloquée car le devis est en cours de validation pour la validation de prix.";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }
    /**
     * Vérifie si le statut du devis bloque la soumission pour la validation de prix (ForVp)
     * 
     * Cette méthode vérifie si le devis est dans un statut qui nécessite une validation de prix
     * avant de pouvoir être soumis (ex: "Validé")
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionForVp(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $blockingStatuses = [
            'Validé'
        ];

        if ($this->isStatusBlockingPartialBeginWith($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Soumission bloquée car le devis doit passer par vérification de prix";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }

    /**
     * Vérifie si le nombre de lignes du devis a été modifié
     * 
     * Cette méthode compare le nombre de lignes actuel avec le nombre de lignes précédent
     * pour détecter les modifications qui pourraient bloquer la soumission
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @return bool true si le nombre de lignes a changé (bloquant), false sinon
     */
    public function isSumOfLineschanged(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($numeroDevis);
        $oldSumOfMontant = $repository->findLatestSumOfMontantByIdentifier($numeroDevis);

        if ($oldSumOfLines === null || $oldSumOfMontant === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return true;
        }

        if (!$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines) || $oldSumOfMontant !== $newSumOfMontant) {
            $message = "soumission bloquée, Le devis soumis est différent de celui envoyé pour vérification de prix car une ou plusieurs lignes ont été ajoutées. Veuillez resoumettre à vérification de prix";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Is blocking
        }

        return true; // Is not blocking
    }

    /**
     * Vérifie si le montant est inchangés et le statut du devis est Prix refusé magasin
     * 
     * Cette méthode compare le montant précédentes et le nouveau montant et aussi le statut actuels est "Prix refusé magasin"
     * pour s'assurer qu'aucune modification n'a été apportée au devis depuis la dernière validation
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param float $newSumOfMontant Le nouveau montant
     * @param string $newStatut Le nouveau statut
     * @return bool true si le montant et le statut sont identiques, false sinon
     */
    public function isSumOfMontantUnchangedAndStatutVp(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        float $newSumOfMontant,
        string $newStatut
    ): bool {
        $oldSumOfMontant = $repository->findLatestSumOfMontantByIdentifier($numeroDevis);
        $oldStatut = $repository->findLatestStatusByIdentifier($numeroDevis);

        if ($oldSumOfMontant === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return true;
        }

        if ($oldSumOfMontant === $newSumOfMontant && $oldStatut === $newStatut) {
            $message = "PM a oublié de modifier les prix dans IPS";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false;
        }

        return true;
    }


    /**
     * Vérifie si le devis existe (dans le cas ou le devis n'existe pas, il faut le soumettre à verification prix)
     * 
     * Cette méthode vérifie si le devis existe dans la base de données
     * 
     * @param LatestSumOfLinesRepositoryInterface $repository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si le devis existe, false sinon
     */
    public function isDevisExiste(
        LatestSumOfLinesRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($numeroDevis);

        if ($oldSumOfLines === null) {
            $message = "le devis doit passer par validation de prix avant de le valider";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // le devis n'existe pas
        }

        return true; // le devis existe
    }
}
