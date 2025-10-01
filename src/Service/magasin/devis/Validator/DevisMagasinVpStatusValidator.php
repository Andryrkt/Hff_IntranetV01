<?php

namespace App\Service\magasin\devis\Validator;

use App\Entity\magasin\devis\DevisMagasin;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;

/**
 * Validateur spécialisé pour les statuts des devis magasin VP
 * 
 * Ce service gère exclusivement la validation des statuts
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpStatusValidator extends ValidationServiceBase
{
    private HistoriqueOperationDevisMagasinService $historiqueService;

    /**
     * Constructeur du validateur de statuts VP
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService)
    {
        $this->historiqueService = $historiqueService;
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
        return $this->validateSimpleBlockingStatus(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_BLOCKING_STATUSES,
            DevisMagasinValidationConfig::ERROR_MESSAGES['status_blocking_vp']
        );
    }

    /**
     * Vérifie si le statut du devis bloque la soumission pour la validation de devis (VD)
     * 
     * Cette méthode empêche l'utilisateur de soumettre un devis à validation de prix
     * si le prix a déjà été vérifié ou si le devis est dans un autre statut bloquant
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionForVd(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        

        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($numeroDevis);

        if ($oldSumOfLines === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return true;
        }

        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_PRIX_VALIDER_AGENCE_BLOCKING_STATUSES,
            $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines),
            "Le prix a été déjà vérifié ... Veuillez soumettre le devis à validation"
        );
    }

    /**
     * Valide un statut bloquant simple
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param array $blockingStatuses Les statuts bloquants
     * @param string $errorMessage Le message d'erreur à afficher
     * @return bool true si la validation passe, false sinon
     */
    private function validateSimpleBlockingStatus(
        StatusRepositoryInterface $repository,
        string $numeroDevis,
        array $blockingStatuses,
        string $errorMessage
    ): bool {
        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $this->historiqueService->sendNotificationSoumission($errorMessage, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }

    /**
     * Valide un statut avec vérification de contenu
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param array $blockingStatuses Les statuts bloquants
     * @param callable $contentCheck Fonction de vérification du contenu
     * @param string $errorMessage Le message d'erreur à afficher
     * @return bool true si la validation passe, false sinon
     */
    private function validateStatusWithContent(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        array $blockingStatuses,
        bool $contentCheck,
        string $errorMessage
    ): bool {
        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses) && $contentCheck()) {
            $this->historiqueService->sendNotificationSoumission($errorMessage, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }
}
