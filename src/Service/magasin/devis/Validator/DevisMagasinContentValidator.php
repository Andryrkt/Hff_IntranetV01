<?php

namespace App\Service\magasin\devis\Validator;

use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;

/**
 * Validateur spécialisé pour le contenu des devis magasin
 * 
 * Ce service gère exclusivement la validation du contenu des devis,
 * incluant l'existence du devis et les modifications de lignes/montants.
 */
class DevisMagasinContentValidator extends ValidationServiceBase
{
    private HistoriqueOperationDevisMagasinService $historiqueService;

    /**
     * Constructeur du validateur de contenu
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService)
    {
        $this->historiqueService = $historiqueService;
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
            $this->sendNotification(
                DevisMagasinValidationConfig::ERROR_MESSAGES['missing_identifier'],
                '',
                false
            );
            return false;
        }
        return true;
    }

    /**
     * Vérifie si le devis existe dans la base de données
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
            $this->sendNotification(
                DevisMagasinValidationConfig::ERROR_MESSAGES['devis_not_exists'],
                $numeroDevis,
                false
            );
            return false;
        }

        return true;
    }

    /**
     * Vérifie si le devis a été modifié (lignes ou montant)
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si aucune modification détectée (non bloquant), false si modifications détectées (bloquant)
     */
    public function isDevisUnchanged(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        // Vérifier les modifications de lignes
        if (!$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)) {
            $this->sendLinesChangedNotification($numeroDevis);
            return false;
        }

        // Vérifier les modifications de montant
        if (!$this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant)) {
            $this->sendAmountChangedNotification($numeroDevis);
            return false;
        }

        return true;
    }

    /**
     * Vérifie si le montant est inchangé et le statut du devis est "Prix modifié"
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param float $newSumOfMontant Le nouveau montant
     * @param array $newStatuts Le nouveau statuts
     * @return bool true si le montant et le statut sont identiques, false sinon
     */
    public function isSumOfMontantUnchangedAndStatutVp(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        float $newSumOfMontant,
        array $newStatuts
    ): bool {
        $oldSumOfMontant = $repository->findLatestSumOfMontantByIdentifier($numeroDevis);
        $oldStatut = $repository->findLatestStatusByIdentifier($numeroDevis);

        if ($oldSumOfMontant === null) {
            return true;
        }

        if ($oldSumOfMontant === $newSumOfMontant && in_array($oldStatut, $newStatuts)) {
            $this->sendNotification(
                DevisMagasinValidationConfig::ERROR_MESSAGES['price_not_modified_in_ips'],
                $numeroDevis,
                false
            );
            return false;
        }

        return true;
    }

    /**
     * Vérifie si le nombre de lignes du devis a été modifié (méthode de compatibilité)
     * 
     * @deprecated Utilisez isDevisUnchanged() à la place
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si aucune modification (non bloquant), false si modifications (bloquant)
     */
    public function isSumOfLinesChanged(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->isDevisUnchanged($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }

    /**
     * Envoie une notification pour les modifications de lignes
     * 
     * @param string $numeroDevis Le numéro de devis concerné
     */
    private function sendLinesChangedNotification(string $numeroDevis): void
    {
        $this->sendNotification(
            DevisMagasinValidationConfig::ERROR_MESSAGES['lines_modified'],
            $numeroDevis,
            false
        );
    }

    /**
     * Envoie une notification pour les modifications de montant
     * 
     * @param string $numeroDevis Le numéro de devis concerné
     */
    private function sendAmountChangedNotification(string $numeroDevis): void
    {
        $this->sendNotification(
            DevisMagasinValidationConfig::ERROR_MESSAGES['amount_modified'],
            $numeroDevis,
            false
        );
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
