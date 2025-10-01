<?php

namespace App\Service\magasin\devis\Validator;

use Symfony\Component\Form\FormInterface;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

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
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(
        HistoriqueOperationDevisMagasinService $historiqueService,
        string $expectedNumeroDevis
    ) {
        $this->fileValidator = new DevisMagasinVpFileValidator($historiqueService, $expectedNumeroDevis);
        $this->statusValidator = new DevisMagasinVpStatusValidator($historiqueService);
        $this->contentValidator = new DevisMagasinVpContentValidator($historiqueService);
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
     * Vérifie si le statut du devis bloque la soumission pour la validation de devis (VD)
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
        return $this->statusValidator->checkBlockingStatusOnSubmissionForVd($repository, $numeroDevis, $newSumOfLines);
    }



    /**
     * Effectue toutes les validations nécessaires avant la validation de prix d'un devis
     * 
     * @param StatusRepositoryInterface $statusRepository Le repository pour accéder aux statuts
     * @param DevisMagasinRepository $devisRepository Le repository pour accéder aux données du devis
     * @param LatestSumOfLinesRepositoryInterface $linesRepository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à valider
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @return bool true si toutes les validations passent, false sinon
     */
    public function validateBeforeVpSubmission(
        StatusRepositoryInterface $statusRepository,
        DevisMagasinRepository $devisRepository,
        LatestSumOfLinesRepositoryInterface $linesRepository,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        // 1. Vérifier si le numéro de devis est manquant
        if (!$this->checkMissingIdentifier($numeroDevis)) {
            return false;
        }

        // 2. verification si le statut est Prix à confirmer
        if (!$this->checkBlockingStatusOnSubmission($statusRepository, $numeroDevis)) {
            return false;
        }

        // 3. Vérifier si le statut est "Prix validé - devis à soumettre (si agence)" et la somme de lignes et montant total sont inchangée
        if (!$this->checkBlockingStatusOnSubmissionForVd($devisRepository, $numeroDevis, $newSumOfLines)) {
            return false;
        }


        return true;
    }
}
