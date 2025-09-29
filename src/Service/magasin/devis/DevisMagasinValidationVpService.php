<?php

namespace App\Service\magasin\devis;

use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Validator\DevisMagasinValidationVpOrchestrator;

/**
 * Service de validation pour les devis magasin - Validation de Prix (VP) (Version refactorisée)
 * 
 * Ce service utilise maintenant l'orchestrateur de validation VP pour déléguer
 * les responsabilités à des validateurs spécialisés.
 * 
 * @deprecated Cette classe est maintenue pour la compatibilité ascendante.
 * Utilisez DevisMagasinValidationVpOrchestrator directement pour les nouveaux développements.
 */
class DevisMagasinValidationVpService extends ValidationServiceBase
{
    private DevisMagasinValidationVpOrchestrator $orchestrator;
    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    /**
     * Constructeur du service de validation de prix des devis magasin
     * 
     * @param HistoriqueOperationDevisMagasinService $historiqueService Service pour l'historique des opérations
     * @param string $expectedNumeroDevis Le numéro de devis attendu pour la validation
     */
    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService, string $expectedNumeroDevis)
    {
        $this->historiqueService = $historiqueService;
        $this->expectedNumeroDevis = $expectedNumeroDevis;
        $this->orchestrator = new DevisMagasinValidationVpOrchestrator($historiqueService, $expectedNumeroDevis);
    }

    /**
     * Vérifie si le numéro de devis est manquant lors de la validation de prix
     * 
     * @param string|null $numeroDevis Le numéro de devis à vérifier
     * @return bool true si le numéro de devis est présent, false sinon
     */
    public function checkMissingIdentifier(?string $numeroDevis): bool
    {
        return $this->orchestrator->checkMissingIdentifier($numeroDevis);
    }

    /**
     * Valide le fichier soumis pour la validation de prix d'un devis magasin
     * 
     * @param FormInterface $form Le formulaire contenant le fichier à valider
     * @return bool true si le fichier est valide, false sinon
     */
    public function validateSubmittedFile(FormInterface $form): bool
    {
        return $this->orchestrator->validateSubmittedFile($form);
    }

    /**
     * Bloqué si le devis est en cours de vérification de prix
     * 
     * @param StatusRepositoryInterface $repository Le repository pour accéder aux statuts
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return bool true si la soumission est autorisée, false si elle est bloquée
     */
    public function checkBlockingStatusOnSubmission(
        StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        return $this->orchestrator->checkBlockingStatusOnSubmission($repository, $numeroDevis);
    }


    /**
     * Effectue toutes les validations nécessaires avant la validation de prix d'un devis (Version améliorée)
     * 
     * @param StatusRepositoryInterface $statusRepository Le repository pour accéder aux statuts
     * @param DevisMagasinRepository $devisRepository Le repository pour accéder aux données du devis
     * @param LatestSumOfLinesRepositoryInterface $linesRepository Le repository pour accéder aux données de lignes
     * @param string $numeroDevis Le numéro de devis à valider
     * @param int $newSumOfLines Le nouveau nombre de lignes
     * @param float $newSumOfMontant Le nouveau montant total
     * @return bool true si toutes les validations passent, false sinon
     */
    public function validateBeforeVpSubmission(
        StatusRepositoryInterface $statusRepository,
        DevisMagasinRepository $devisRepository,
        string $numeroDevis,
        int $newSumOfLines,
        float $newSumOfMontant
    ): bool {
        return $this->orchestrator->validateBeforeVpSubmission($statusRepository, $devisRepository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
    }
}
