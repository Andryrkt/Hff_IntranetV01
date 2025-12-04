<?php

namespace App\Traits\Validator;

use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Config\DevisMagasinValidationConfig;
use App\Service\SessionManagerService;

/**
 * Trait pour envoyer des notifications de validation.
 *
 * Ce trait nécessite que la classe qui l'utilise possède une propriété
 * `$historiqueService` de type `HistoriqueOperationDevisMagasinService`.
 */
trait ValidatorNotificationTrait
{
    private function getSessionService()
    {
        global $container;
        return $container->get(SessionManagerService::class);
    }

    private function getHistoriqueService(): HistoriqueOperationDevisMagasinService
    {
        global $container;
        return $container->get(HistoriqueOperationDevisMagasinService::class);
    }

    /**
     * Envoie une notification via le service d'historique.
     *
     * @param string $message     Le message à envoyer.
     * @param string $numeroDevis Le numéro de devis concerné.
     * @param bool   $success     Indique si l'opération a réussi.
     * @param array|null $structuredParams Tableau structuré des paramètres de recherche (session).
     * @param string $paramPrefix Name de l'input de recherche.
     * @param array $routeParams  Paramètres de la route.
     * @param array|null $queryParams  Paramètres de la requête.
     */
    private function sendNotificationDevisMagasin(
        string $message,
        string $numeroDevis,
        bool $success
    ): void {

        $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');
        $nomInputSearch = 'devis_magasin_search'; // initialistion de nom de chaque champ ou input
        $this->getHistoriqueService()->sendNotificationSoumission(
            $message,
            $numeroDevis,
            DevisMagasinValidationConfig::REDIRECT_ROUTE,
            $success,
            $criteria,
            $nomInputSearch
        );
    }
}
