<?php

namespace App\Service\magasin\devis\Validator;

use App\Entity\magasin\devis\DevisMagasin;
use App\Service\validation\ValidationServiceBase;
use App\Traits\Validator\ValidatorNotificationTrait;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\SessionManagerService;
use Illuminate\Support\Facades\Session;

/**
 * Validateur spécialisé pour les statuts des devis magasin VP
 * 
 * Ce service gère exclusivement la validation des statuts
 * pour la validation de prix des devis magasin.
 */
class DevisMagasinVpStatusValidator extends ValidationServiceBase
{
    use ValidatorNotificationTrait;

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
     * Vérifie si le statut du devis est Prix validé - devis à soumettre (si agence) et somme de lignes et montant inchangé
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verifierStatutPrixValideAgenceEtSommeDeLignesAndAmountInchangée(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_PRIX_VALIDER_AGENCE_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['vp_prix_valide_agence_et_somme_de_lignes_et_amount_inchangé']
        );
    }

    /**
     * Vérifie si le statut du devis est Prix modifié - devis à soumettre (si agence) et somme de lignes inchangée et montant changé
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données du devis
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutPrixModifierAgenceEtSommeDeLignesInchangéeEtMontantchange(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_PRIX_MODIFIER_AGENCE_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && !$this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['vp_prix_modifier_agence_et_somme_de_lignes_et_amount_inchangé']
        );
    }



    public function verificationStatutValideAEnvoyerAuclientEtSommeDeLignesChangeEtMontantChange(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_VALIDE_A_ENVOYER_AU_CLIENT_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return !$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['vp_valide_a_envoyer_au_client_et_somme_de_lignes_changeet_amount_inchange']
        );
    }

    /**
     * Vérifie si le statut est "A valider chef d'agence"
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verifieStatutAvalideChefAgence(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        return $this->validateSimpleBlockingStatus(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_BLOCKING_STATUTS_VALIDE_CHEF_AGENCE,
            DevisMagasinValidationConfig::ERROR_MESSAGES['status_blocking_general']
        );
    }

    /**
     * Vérifie si le statut est "A valider chef d'agence"
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verifieStatutValideAEnvoyerAuclientEtSommeMontantInchange(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        return $this->validateSimpleBlockingStatus(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_BLOCKING_STATUTS_VALIDE_CHEF_AGENCE,
            DevisMagasinValidationConfig::ERROR_MESSAGES['status_blocking_general']
        );
    }


    public function verifieStatutValideAEnvoyerAuclientEtSommeLignesInchange(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_BLOCKING_STATUTS_VALIDE_A_ENVOYER_AU_CLIENT_ET_SOMME_LINES_INCHANGE,
            function () use ($repository, $numeroDevis, $newSumOfLines) {
                return $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['vp_valide_a_envoyer_au_client_et_somme_lignes_inchange']
        );
    }

    public function verifieStatutClotureAModifierEtSommeLignesIpsInferieurSommeLignesDevis(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VP_BLOCKING_STATUTS_CLOTURE_A_MODIFIER_ET_SOMME_MONTANT_IPS_INFERIEUR_SOMME_MONTANT_DEVIS,
            function () use ($repository, $numeroDevis, $newSumOfLines) {
                return !$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['vp_cloture_a_modifier_et_somme_montant_ips_inferieur_somme_montant_devis']
        );
    }

    //-----------------------------------------------------------------

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
}
