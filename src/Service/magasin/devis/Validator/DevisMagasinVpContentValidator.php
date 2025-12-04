<?php

namespace App\Service\magasin\devis\Validator;

use App\Service\validation\ValidationServiceBase;
use App\Traits\Validator\ValidatorNotificationTrait;

/**
 * Validateur spécialisé pour le contenu des devis magasin VP
 * 
 * Ce service gère exclusivement la validation du contenu
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpContentValidator extends ValidationServiceBase
{
    use ValidatorNotificationTrait;

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
            $this->sendNotificationDevisMagasin($message, '', 'devis_magasin_liste', false);
            return false; // Validation failed
        }
        return true; // Validation passed
    }
}
