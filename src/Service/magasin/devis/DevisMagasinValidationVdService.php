<?php

namespace App\Service\magasin\devis;

use Symfony\Component\Form\FormInterface;   
use App\Service\validation\ValidationServiceBase;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Validator\DevisMagasinValidationOrchestrator;

/**
 * Service de validation pour les devis magasin (Version refactorisée)
 * 
 * Ce service utilise maintenant l'orchestrateur de validation pour déléguer
 * les responsabilités à des validateurs spécialisés.
 * 
 * @deprecated Cette classe est maintenue pour la compatibilité ascendante.
 * Utilisez DevisMagasinValidationOrchestrator directement pour les nouveaux développements.
 */
class DevisMagasinValidationVdService extends ValidationServiceBase
{
    private DevisMagasinValidationOrchestrator $orchestrator;
    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du service de validation des devis magasin
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService, string $expectedNumeroDevis)
    {
        $this->historiqueService = $historiqueService;
        $this->expectedNumeroDevis = $expectedNumeroDevis;
        $this->orchestrator = new DevisMagasinValidationOrchestrator($historiqueService, $expectedNumeroDevis);
    }

    /**
     * Valide le fichier soumis pour un devis magasin
     * 
     * @param FormInterface $form Le formulaire contenant le fichier à valider
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form): bool
    {
        return $this->orchestrator->validateSubmittedFile($form);
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
        return $this->orchestrator->isDevisUnchanged($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
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
        return $this->orchestrator->isSumOfLinesChanged($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
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
        return $this->orchestrator->isSumOfMontantUnchangedAndStatutVp($repository, $numeroDevis, $newSumOfMontant, $newStatuts);
    }





    /**
     * Effectue toutes les validations nécessaires avant la soumission d'un devis
     * 
     * @param DevisMagasinRepository $repository Le repository pour accéder aux données
     * @param string $numeroDevis Le numéro de devis à valider
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return void
     * @deprecated Utilisez validateBeforeSubmission() qui retourne un booléen au lieu d'utiliser exit
     */
    public function validationAvantSoumission(
        DevisMagasinRepository $repository,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): void {
        if (!$this->orchestrator->validateBeforeSubmission($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant)) {
            exit; // ⚠️ DANGEREUX - maintenu pour compatibilité
        }
    }

    /**
     * Effectue toutes les validations nécessaires avant la soumission d'un devis (Version améliorée)
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
        return $this->orchestrator->validateBeforeSubmission($repository, $listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }
}
