<?php

namespace App\Service\magasin\devis\Validator;

use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * Validateur spécialisé pour le contenu des devis magasin VP
 * 
 * Ce service gère exclusivement la validation du contenu
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpContentValidator extends ValidationServiceBase
{
    private HistoriqueOperationDevisMagasinService $historiqueService;

    /**
     * Constructeur du validateur de contenu VP
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService)
    {
        $this->historiqueService = $historiqueService;
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

}
