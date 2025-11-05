<?php

namespace App\Service\magasin\bc;

use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\magasin\bc\HistoriqueOperationBcMagasinService;

class BcMagasinValidationService extends ValidationServiceBase
{
    private const STATUT_EN_COURS_VALIDATION = [
        BcMagasin::STATUT_SOUMIS_VALIDATION
    ];
    private const ERROR_MESSAGES = [
        'missing_identifier' => 'Le numéro de Devis est manquant.',
        'blocage_statut_En_cours_validation' => 'Le BC est en cours de validation',
        'statut_devis_et_bc_coherents' => 'on ne peut pas soumettre un BC à validation que si le devis est envoyé au client et la reception du bc est en attente.'
    ];

    // Routes de redirection
    private const REDIRECT_ROUTE = 'devis_magasin_liste';


    private HistoriqueOperationBcMagasinService $historiqueService;

    public function __construct()
    {
        global $container;
        $this->historiqueService = $container->get(HistoriqueOperationBcMagasinService::class);
    }

    public function validateData(array $data): bool
    {
        if (!$this->checkMissingIdentifier($data['numeroDevis'])) {
            return false;
        }

        if (!$this->checkBlockingStatusOnSubmissionIfStatusVp($data['bcRepository'], $data['numeroDevis'])) {
            
            return false;
        }

        if (!$this->BloquerSiStatutDevisEtBcCorrespond($data['devisMagasinRepository'], $data['numeroDevis'])) {
            return false;
        }

        return true;
    }

    // ==============================================================================================
    /**
     * Vérifie si le numéro de devis est manquant lors de la soumission
     * 
     * @param string|null $numeroDevis Le numéro de devis à vérifier
     * @return bool true si le numéro de devis est présent, false sinon
     */
    public function checkMissingIdentifier(?string $numeroDevis): bool
    {
        if ($this->isIdentifierMissing($numeroDevis)) {
            $this->sendNotification(
                self::ERROR_MESSAGES['missing_identifier'],
                '-',
                false
            );
            return false;
        }
        return true;
    }

    /**
     * verifie si le statut le plus récent est bloquant pour la soumission 
     * exemple: le statut est "En cours de validation"
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
            self::STATUT_EN_COURS_VALIDATION,
            self::ERROR_MESSAGES['blocage_statut_En_cours_validation']
        );
    }

    public function BloquerSiStatutDevisEtBcCorrespond(
        DevisMagasinRepository $devisMagasinRepository,
        string $numeroDevis
    ) {
        $statut = $devisMagasinRepository->getStatutDwEtStatutBc($numeroDevis);

        if ($statut && $statut['statutDw'] != DevisMagasin::STATUT_ENVOYER_CLIENT && $statut['statutBc'] != BcMagasin::STATUT_EN_ATTENTE_BC) {
            $this->sendNotification(
                self::ERROR_MESSAGES['statut_devis_et_bc_coherents'],
                $numeroDevis,
                false
            );
            return false;
        }

        return true;
    }

    // ===============================================================================
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
            $this->sendNotification($errorMessage, $numeroDevis, false);
            return false;
        }

        return true;
    }

    /**
     * Envoie une notification via le service d'historique
     * 
     * @param string $message Le message à envoyer
     * @param string $numeroDevis Le numéro de devis concerné
     * @param bool $success Indique si l'opération a réussi
     */
    private function sendNotification(string $message, string $numeroDevis, bool $success): void
    {
        $this->historiqueService->sendNotificationSoumission(
            $message,
            $numeroDevis,
            self::REDIRECT_ROUTE,
            $success
        );
    }

    public function getRedirectRoute(): string
    {
        return self::REDIRECT_ROUTE;
    }
}
