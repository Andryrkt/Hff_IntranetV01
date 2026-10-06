<?php

namespace App\Service\magasin\devis;

use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Service\validation\ValidationServiceBase;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Traits\Validator\ValidatorNotificationTrait;

class DevisMagasinEnvoyerAuClientValidatorService extends ValidationServiceBase
{
    use ValidatorNotificationTrait;

    private HistoriqueOperationDevisMagasinService $historiqueService;

    /**
     * Constructeur du validateur de statuts Envoyer au client
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     */
    public function __construct()
    {
        global $container;
        $this->historiqueService = $container->get(HistoriqueOperationDevisMagasinService::class);
    }

    public function validateData(array $data): bool
    {
        //Bloquer si :

        // s'il n'y a pas de numéro de devis
        if (!$this->checkMissingIdentifier($data['numeroDevis'])) {
            return false;
        }

        // si le statut est Prix à confirmer
        if (!$this->verifierStatutPrixAConfirmer($data['devisMagasinRepository'], $data['numeroDevis'])) {
            return false;
        }

        // si le statut est Prix modifié et il n'y a pas de modification de prix sur le devis dans IPS
        if (!$this->verifierStatutPrixModifieOuPasDeModificationPrix($data['devisMagasinRepository'], $data['numeroDevis'], $data['newSumOfMontant'])) {
            return false;
        }

        return true;
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
            $this->sendNotificationDevisMagasin(
                DevisMagasinValidationConfig::ERROR_MESSAGES['missing_identifier'],
                '-',
                false
            );
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

    /**
     * Vérifier si le statut est Prix modifié et il n'y a pas de modification de prix sur le devis dans IPS
     * 
     * @param DevisMagasinRepository $devisRepository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la validation passe, false sinon
     */
    public function verifierStatutPrixModifieOuPasDeModificationPrix(DevisMagasinRepository $repository, string $numeroDevis, float $newSumOfMontant): bool
    {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::POINTAGE_PRIX_MODIFIER_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfMontant) {
                return $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['pointage_prix_modifier_montant_inchanger']
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
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
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
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
            return false;
        }
        return true;
    }

    public function getRedirectRoute(): string
    {
        return DevisMagasinValidationConfig::REDIRECT_ROUTE;
    }
}
