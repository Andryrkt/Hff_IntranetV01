<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * Orchestrateur de validation pour les devis magasin
 * 
 * Ce service coordonne tous les validateurs spécialisés pour effectuer
 * une validation complète des devis magasin avant soumission.
 */
class DevisMagasinValidationOrchestrator
{
    private DevisMagasinFileValidator $fileValidator;
    private DevisMagasinStatusValidator $statusValidator;
    private DevisMagasinContentValidator $contentValidator;

    /**
     * Constructeur de l'orchestrateur de validation
     * 
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(
        string $expectedNumeroDevis
    ) {
        $this->fileValidator = new DevisMagasinFileValidator($expectedNumeroDevis);
        $this->statusValidator = new DevisMagasinStatusValidator();
        $this->contentValidator = new DevisMagasinContentValidator();
    }

    /**
     * Valide le fichier soumis pour un devis magasin
     * 
     * @param FormInterface $form Le formulaire contenant le fichier à valider
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form): bool
    {
        return $this->fileValidator->validateSubmittedFile($form);
    }

    /**
     * Effectue toutes les validations nécessaires avant la soumission d'un devis
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données
     * @param string $numeroDevis Le numéro de devis à valider
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si toutes les validations passent, false sinon
     */
    public function validateBeforeSubmission(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        // 1. Vérifier si le numéro de devis est manquant
        if (!$this->contentValidator->checkMissingIdentifier($numeroDevis)) {
            return false;
        }

        // 2. Vérifier si le devis existe
        if (!$this->contentValidator->isDevisExiste($repository, $numeroDevis)) {
            return false;
        }

        // 3. Vérifier si le statut du devis est "Prix à confirmer"
        if (!$this->statusValidator->checkBlockingStatusOnSubmissionIfStatusVp($repository, $numeroDevis)) {
            return false;
        }

        // 4. Vérifier si le statut du devis est "Prix validé" et la somme de lignes et le montant sont inchangés
        if (!$this->statusValidator->checkBlockingStatusOnSubmissionForVp($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 5. Vérifier si le statut du devis est "Prix modifié" et la somme de lignes inchangée mais le montant est changé
        if (!$this->statusValidator->verificationStatutChangementDeMontantMaisPasLignePourVp($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 6. Vérifier si le statut du devis est "Prix validé" et la somme de lignes change mais le montant reste inchangé
        if (!$this->statusValidator->verificationStatutChangementDeligneMaisPasMontantPourVp($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }


        // 7. Vérifié si le statut du devis est "Prix modifié - devis à envoyer au client (si Tana)" et la somme de lignes change mais le montant est inchangé
        if (!$this->statusValidator->verificationStatutChangementDeligneMaisPasMontant($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 8. Vérifié si le statut du devis est "Demande refusée par le PM"
        if (!$this->statusValidator->verificationStatutDemandeRefuseParPm($repository, $numeroDevis)) {
            return false;
        }

        // 9. Vérifier si le statut du devis est bloquant pour la soumission générale
        if (!$this->statusValidator->checkBlockingStatusOnSubmission($repository, $numeroDevis)) {
            return false;
        }

        // 10. Vérifier si le montant total du devis IPS est inchangé
        if (!$this->statusValidator->verificationStatutMontantTotalInchangerParRapportAuDevisIps($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfMontant)) {
            return false;
        }

        // 11. Vérifier si le nombre de lignes et le montant total du devis IPS sont inchangés
        if (!$this->statusValidator->verificationStatutLignesTotalAmountModifiedParRapportAuDevisIps($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 12. Vérifier si le nombre de lignes du devis IPS est inchangé
        if (!$this->statusValidator->verificationStatutLignesTotalInchanger($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines)) {
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
     * @return bool true si aucune modification détectée, false si modifications détectées
     */
    public function isDevisUnchanged(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->contentValidator->isDevisUnchanged($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
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
        return $this->contentValidator->isSumOfMontantUnchangedAndStatutVp($repository, $numeroDevis, $newSumOfMontant, $newStatuts);
    }

    /**
     * Vérifie si le nombre de lignes du devis a été modifié (méthode de compatibilité)
     * 
     * @deprecated Utilisez isDevisUnchanged() à la place
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si aucune modification, false si modifications
     */
    public function isSumOfLinesChanged(
        DevisMagasinRepository $repository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->contentValidator->isSumOfLinesChanged($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }
}
