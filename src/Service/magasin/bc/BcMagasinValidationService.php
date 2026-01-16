<?php

namespace App\Service\magasin\bc;

use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\validation\ValidationServiceBase;
use App\Repository\Interfaces\StatusRepositoryInterface;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\historiqueOperation\magasin\bc\HistoriqueOperationBcMagasinService;
use App\Traits\Validator\ValidatorNotificationTrait;

class BcMagasinValidationService extends ValidationServiceBase
{
    use ValidatorNotificationTrait;

    private const STATUT_BC_EN_COURS_VALIDATION = [
        BcMagasin::STATUT_SOUMIS_VALIDATION
    ];
    private const ERROR_MESSAGES = [
        'missing_identifier' => 'Le numéro de Devis est manquant.',
        'blocage_statut_En_cours_validation' => 'Le BC est en cours de validation',
        'statut_devis_et_bc_coherents' => 'on ne peut pas soumettre un BC à validation que si le devis est envoyé au client et la reception du bc est en attente.',
        'statut_devis_a_traiter' => 'on ne peut pas soumettre un BC à validation que si la statut de la devis est encore "A traiter".'

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
    {   //Bloquer si :

        // s'il n'y a pas de numéro de devis
        if (!$this->checkMissingIdentifier($data['numeroDevis'])) {
            return false;
        }

        // le statut BC est en cours de validation
        if (!$this->checkBlockingStatusOnSubmissionIfStatusVp($data['bcRepository'], $data['numeroDevis'])) {
            return false;
        }

        // le statut de devis "à envoyer client" et le statut BC "en attent BC"
        if (!$this->BloquerSiStatutDevisEtBcCorrespond($data['devisMagasinRepository'], $data['numeroDevis'])) {
            return false;
        }

        // le statut de devis "A traiter"
        if (!$this->BloquerSiStatutDevisEstATraiter($data['devisMagasinRepository'], $data['numeroDevis'])) {
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
            $this->sendNotificationDevisMagasin(
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
            self::STATUT_BC_EN_COURS_VALIDATION,
            self::ERROR_MESSAGES['blocage_statut_En_cours_validation']
        );
    }

    public function BloquerSiStatutDevisEtBcCorrespond(
        DevisMagasinRepository $devisMagasinRepository,
        string $numeroDevis
    ) {
        //recupération du statut devis
        $statut = $devisMagasinRepository->getStatutDwEtStatutBc($numeroDevis);

        if ($statut && $statut['statutDw'] != DevisMagasin::STATUT_ENVOYER_CLIENT || $statut['statutBc'] != BcMagasin::STATUT_EN_ATTENTE_BC) {
            $this->sendNotificationDevisMagasin(
                self::ERROR_MESSAGES['statut_devis_et_bc_coherents'],
                $numeroDevis,
                false
            );
            return false;
        }

        return true;
    }

    public function BloquerSiStatutDevisEstATraiter(
        DevisMagasinRepository $devisMagasinRepository,
        string $numeroDevis
    ) {
        //recupération du statut devis
        $statut = $devisMagasinRepository->getStatutDwEtStatutBc($numeroDevis);

        if (!$statut) {
            $this->sendNotificationDevisMagasin(
                self::ERROR_MESSAGES['statut_devis_a_traiter'],
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
            $this->sendNotificationDevisMagasin($errorMessage, $numeroDevis, false);
            return false;
        }

        return true;
    }


    public function getRedirectRoute(): string
    {
        return self::REDIRECT_ROUTE;
    }
}
