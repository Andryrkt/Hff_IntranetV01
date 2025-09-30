<?php

namespace App\Service\magasin\devis;

use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Service\validation\ValidationServiceBase;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

class DevisMagasinEnvoyerAuClientValidatorService extends ValidationServiceBase
{

    private HistoriqueOperationDevisMagasinService $historiqueService;

    /**
     * Constructeur du validateur de statuts Envoyer au client
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService)
    {
        $this->historiqueService = $historiqueService;
    }

    public function validateBeforeEnvoyerAuClient(DevisMagasinRepository $devisRepository, string $numeroDevis): bool
    {
        // 1. Vérifier si le statut est Prix à confirmer
        if (!$this->verifierStatutPrixAConfirmer($devisRepository, $numeroDevis)) {
            return false;
        }

        return true;
    }
    /**
     * Vérifier si le statut est Prix à confirmer
     * 
     * @param DevisMagasinRepository $devisRepository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la validation passe, false sinon
     */
    public function verifierStatutPrixAConfirmer(DevisMagasinRepository $devisRepository, string $numeroDevis): bool
    {
        return $this->validateSimpleBlockingStatus(
            $devisRepository,
            $numeroDevis,
            DevisMagasinValidationConfig::POINTAGE_PRIX_A_CONFIRMER_BLOCKING_STATUSES,
            DevisMagasinValidationConfig::ERROR_MESSAGES['status_blocking_vp']
        );
    }


 
    // ---------------------------------------------------------------------------------------------------

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

    private function validateStatusWithContent(
        StatusRepositoryInterface $repository,
        string $numeroDevis,
        array $blockingStatuses,
        callable $conditionCallback,
        string $errorMessage
    ): bool {
        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses) && $conditionCallback()) {
            $this->historiqueService->sendNotificationSoumission($errorMessage, $numeroDevis, 'devis_magasin_liste', false);
            return false;
        }
        return true;
    }
}
