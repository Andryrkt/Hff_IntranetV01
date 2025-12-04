<?php

namespace App\Service\magasin\devis\Validator;

use App\Service\validation\ValidationServiceBase;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * Validateur spécialisé pour les statuts des devis magasin
 * 
 * Ce service gère exclusivement la validation des statuts
 * pour déterminer si un devis peut être soumis ou non.
 */
class DevisMagasinStatusValidator extends ValidationServiceBase
{
    use \App\Traits\Validator\ValidatorNotificationTrait;



    /**
     * Vérifie si le statut du devis est "Prix à confirmer" lors de la soumission
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionIfStatusVp(
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
     * Vérifie si le statut du devis est "Prix validé" et la somme de lignes et le montant sont inchangés
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmissionForVp(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_PRICE_VALIDATED_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isStatusBlockingPartialBeginWith($repository, $numeroDevis, DevisMagasinValidationConfig::VD_PRICE_VALIDATED_BLOCKING_STATUSES)
                    && $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['price_already_validated']
        );
    }

    /**
     * Vérifie si le statut du devis est "Prix validé" et la somme de lignes change mais le montant reste inchangé
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutChangementDeligneMaisPasMontantPourVp(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_LINES_CHANGE_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isStatusBlockingPartialBeginWith($repository, $numeroDevis, DevisMagasinValidationConfig::VD_LINES_CHANGE_BLOCKING_STATUSES)
                    && !$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['lines_changed']
        );
    }

    /**
     * Vérifie si le statut du devis est "Prix modifié" et la somme de lignes inchangée mais le montant est changé
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutChangementDeMontantMaisPasLignePourVp(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_PRICE_MODIFIED_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isStatusBlockingPartialBeginWith($repository, $numeroDevis, DevisMagasinValidationConfig::VD_PRICE_MODIFIED_BLOCKING_STATUSES)
                    && $this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && !$this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['price_already_validated']
        );
    }

    /**
     * Vérifie si le statut du devis est "Prix modifié - devis à envoyer au client (si Tana)" et la somme de lignes change mais le montant est inchangé
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutChangementDeligneMaisPasMontant(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithContent(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_LINES_CHANGE_BLOCKING_STATUSES,
            function () use ($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                return $this->isStatusBlockingPartialBeginWith($repository, $numeroDevis, DevisMagasinValidationConfig::VD_LINES_CHANGE_BLOCKING_STATUSES)
                    && !$this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)
                    && $this->isSumOfMontantUnchanged($repository, $numeroDevis, $newSumOfMontant);
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['statut_modified']
        );
    }

    /**
     * Vérifie si le statut du devis est "Demande refusée par le PM"
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutDemandeRefuseParPm(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        return $this->validateSimpleBlockingStatus(
            $repository,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_DEMANDE_REFUSE_PAR_PM,
            DevisMagasinValidationConfig::ERROR_MESSAGES['demande_refuse_par_pm']
        );
    }

    /**
     * Vérifie si le statut du devis bloque la soumission générale
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
            DevisMagasinValidationConfig::BLOCKING_STATUSES,
            DevisMagasinValidationConfig::ERROR_MESSAGES['status_blocking_general']
        );
    }

    /**
     * Verifie si le statut est "Validé - à envoyer au client" et le montant total du devis ips n'est pas different du montant total du devis
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param ListeDevisMagasinModel $listeDevisMagasinModel Le modèle pour accéder aux données IPS
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function verificationStatutMontantTotalInchangerParRapportAuDevisIps(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithIpsData(
            $repository,
            $listeDevisMagasinModel,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_AMOUNT_MODIFIED_BLOCKING_STATUSES,
            function () use ($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfMontant) {
                $montantTotalDevisIps = $listeDevisMagasinModel->getMontantTotalDevisIps($numeroDevis);
                return $this->isStatusBlocking($repository, $numeroDevis, DevisMagasinValidationConfig::VD_AMOUNT_MODIFIED_BLOCKING_STATUSES)
                    && $montantTotalDevisIps !== $newSumOfMontant;
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['amount_modified']
        );
    }

    public function verificationStatutLignesTotalAmountModifiedParRapportAuDevisIps(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->validateStatusWithIpsData(
            $repository,
            $listeDevisMagasinModel,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_LINES_AMOUNT_MODIFIED_BLOCKING_STATUSES,
            function () use ($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant) {
                $lignesTotalDevisIps = $listeDevisMagasinModel->getLignesTotalDevisIps($numeroDevis);
                $montantTotalDevisIps = $listeDevisMagasinModel->getMontantTotalDevisIps($numeroDevis);
                return $this->isStatusBlocking($repository, $numeroDevis, DevisMagasinValidationConfig::VD_LINES_AMOUNT_MODIFIED_BLOCKING_STATUSES)
                    && $lignesTotalDevisIps > $newSumOfLines && $montantTotalDevisIps !== $newSumOfMontant;
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['lines_amount_modified']
        );
    }

    public function verificationStatutLignesTotalInchanger(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        return $this->validateStatusWithIpsData(
            $repository,
            $listeDevisMagasinModel,
            $numeroDevis,
            DevisMagasinValidationConfig::VD_LINES_TOTAL_MODIFIED_BLOCKING_STATUSES,
            function () use ($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines) {
                $lignesTotalDevisIps = $listeDevisMagasinModel->getLignesTotalDevisIps($numeroDevis);
                return $this->isStatusBlocking($repository, $numeroDevis, DevisMagasinValidationConfig::VD_LINES_TOTAL_MODIFIED_BLOCKING_STATUSES)
                    && $lignesTotalDevisIps > $newSumOfLines;
            },
            DevisMagasinValidationConfig::ERROR_MESSAGES['lines_modified']
        );
    }

    /**
     * Méthode générique pour valider les statuts bloquants simples
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param array $blockingStatuses Les statuts bloquants à vérifier
     * @param string $errorMessage Le message d'erreur à afficher
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    private function validateSimpleBlockingStatus(
        StatusRepositoryInterface $repository,
        string $numeroDevis,
        array $blockingStatuses,
        string $errorMessage
    ): bool {
        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
            return false;
        }

        return true;
    }

    /**
     * Méthode générique pour valider les statuts avec comparaison de contenu
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param array $blockingStatuses Les statuts bloquants à vérifier
     * @param callable $conditionCallback Fonction de callback pour la condition spécifique
     * @param string $errorMessage Le message d'erreur à afficher
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    private function validateStatusWithContent(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        array $blockingStatuses,
        callable $conditionCallback,
        string $errorMessage
    ): bool {
        if ($conditionCallback()) {
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
            return false;
        }

        return true;
    }

    /**
     * Méthode générique pour valider les statuts avec ListeDevisMagasinModel
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux statuts
     * @param ListeDevisMagasinModel $listeDevisMagasinModel Le modèle pour accéder aux données IPS
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param array $blockingStatuses Les statuts bloquants à vérifier
     * @param callable $conditionCallback Fonction de callback pour la condition spécifique
     * @param string $errorMessage Le message d'erreur à afficher
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    private function validateStatusWithIpsData(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        array $blockingStatuses,
        callable $conditionCallback,
        string $errorMessage
    ): bool {
        if ($conditionCallback()) {
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
            return false;
        }

        return true;
    }
}
