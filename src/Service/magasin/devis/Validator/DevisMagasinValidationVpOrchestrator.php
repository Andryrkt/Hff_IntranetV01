<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;

/**
 * Orchestrateur de validation pour les devis magasin - Validation de Prix (VP)
 * 
 * Ce service coordonne tous les validateurs spécialisés pour effectuer
 * une validation complète des devis magasin avant validation de prix.
 */
class DevisMagasinValidationVpOrchestrator
{
    private DevisMagasinVpFileValidator $fileValidator;
    private DevisMagasinVpStatusValidator $statusValidator;
    private DevisMagasinVpContentValidator $contentValidator;

    /**
     * Constructeur de l'orchestrateur de validation VP
     * 
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(
        string $expectedNumeroDevis,
        string $remoteUrl = ""
    ) {
        $this->fileValidator = new DevisMagasinVpFileValidator($expectedNumeroDevis, $remoteUrl);
        $this->statusValidator = new DevisMagasinVpStatusValidator();
        $this->contentValidator = new DevisMagasinVpContentValidator();
    }

    /**
     * Valide le fichier soumis pour un devis magasin VP
     * 
     * @param FormInterface $form Le formulaire contenant le fichier à valider
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form): bool
    {
        return $this->fileValidator->validateSubmittedFile($form);
    }

    /**
     * Vérifie si le numéro de devis est manquant
     * 
     * @param string|null $numeroDevis Le numéro de devis à vérifier
     * @return bool true si le numéro de devis est présent, false sinon
     */
    public function checkMissingIdentifier(?string $numeroDevis): bool
    {
        return $this->contentValidator->checkMissingIdentifier($numeroDevis);
    }

    /**
     * Vérifie si le statut du devis bloque la soumission pour la validation de prix
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmission(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        return $this->statusValidator->checkBlockingStatusOnSubmission($repository, $numeroDevis);
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
        return $this->statusValidator->verifierStatutPrixValideAgenceEtSommeDeLignesAndAmountInchangée($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }

    public function verificationStatutPrixModifierAgenceEtSommeDeLignesInchangéeEtMontantchange(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->statusValidator->verificationStatutPrixModifierAgenceEtSommeDeLignesInchangéeEtMontantchange($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }

    public function verificationStatutValideAEnvoyerAuclientEtSommeDeLignesChangeEtMontantChange(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->statusValidator->verificationStatutValideAEnvoyerAuclientEtSommeDeLignesChangeEtMontantChange($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }

    public function verifieStatutAvalideChefAgence(DevisMagasinRepository $repository, string $numeroDevis): bool
    {
        return $this->statusValidator->verifieStatutAvalideChefAgence($repository, $numeroDevis);
    }

    public function verifieStatutValideAEnvoyerAuclientEtSommeLignesInchange(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines, float $newSumOfMontant): bool
    {
        return $this->statusValidator->verifieStatutValideAEnvoyerAuclientEtSommeLignesInchange($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }

    public function verifieStatutClotureAModifierEtSommeLignesIpsInferieurSommeLignesDevis(DevisMagasinRepository $repository, string $numeroDevis, int $newSumOfLines): bool
    {
        return $this->statusValidator->verifieStatutClotureAModifierEtSommeLignesIpsInferieurSommeLignesDevis($repository, $numeroDevis, $newSumOfLines);
    }

    /**
     * Effectue toutes les validations nécessaires avant la validation de prix d'un devis
     * 
     * @param DevisMagasinRepository $devisRepository Le repository pour accéder aux données du devis
     * @param LatestSumOfLinesRepositoryInterface $linesRepository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à valider
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @return bool true si toutes les validations passent, false sinon
     */
    public function validateBeforeVpSubmission(
        DevisMagasinRepository $devisRepository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        // 1. Vérifier si le numéro de devis est manquant
        if (!$this->checkMissingIdentifier($numeroDevis)) {
            return false;
        }

        // 2. verification si le statut est Prix à confirmer
        if (!$this->checkBlockingStatusOnSubmission($devisRepository, $numeroDevis)) {
            return false;
        }

        // 3. Vérifier si le statut est Prix validé - devis à soumettre (si agence) et somme de lignes et montant inchangé
        if (!$this->verifierStatutPrixValideAgenceEtSommeDeLignesAndAmountInchangée($devisRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 4. Vérifier si le statut est Prix modifié - devis à soumettre (si agence) et somme de lignes inchangée et montant changé
        if (!$this->verificationStatutPrixModifierAgenceEtSommeDeLignesInchangéeEtMontantchange($devisRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 5. Vérifier si le statut est Validé - à envoyer au client et somme de lignes change et montant change
        if (!$this->verificationStatutValideAEnvoyerAuclientEtSommeDeLignesChangeEtMontantChange($devisRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 6. Vérifier si le statut est A valider chef d'agence
        if (!$this->verifieStatutAvalideChefAgence($devisRepository, $numeroDevis)) {
            return false;
        }

        // 7. Vérifier si le statut est Validé - à envoyer au client et somme de lignes inchangée
        if (!$this->verifieStatutValideAEnvoyerAuclientEtSommeLignesInchange($devisRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            return false;
        }

        // 8. Vérifier si le statut est Cloturé - A modifier et somme de lignes IPS inférieure à somme de lignes devis
        if (!$this->verifieStatutClotureAModifierEtSommeLignesIpsInferieurSommeLignesDevis($devisRepository, $numeroDevis, $newSumOfLines)) {
            return false;
        }

        return true;
    }
}
