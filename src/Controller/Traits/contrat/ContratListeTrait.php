<?php

namespace App\Controller\Traits\contrat;

trait ContratListeTrait
{
    private function initialisationContrat($contratSearch, $em)
    {
        $criteria = $this->getSessionService()->get('contrat_search_criteria', []);
        
        if ($criteria !== null && !empty($criteria)) {
            // Définir les critères de recherche depuis la session
            if (isset($criteria['reference']) && $criteria['reference'] !== null) {
                $contratSearch->setReferenceSearch($criteria['reference']);
            }

            if (isset($criteria['statut']) && $criteria['statut'] !== null) {
                $contratSearch->setStatut($criteria['statut']);
            }

            if (isset($criteria['agence']) && $criteria['agence'] !== null) {
                $contratSearch->setAgenceSearch($criteria['agence']);
            }

            if (isset($criteria['service']) && $criteria['service'] !== null) {
                $contratSearch->setServiceSearch($criteria['service']);
            }

            if (isset($criteria['nom_partenaire']) && $criteria['nom_partenaire'] !== null) {
                $contratSearch->setNomPartenaireSearch($criteria['nom_partenaire']);
            }

            if (isset($criteria['type_tiers']) && $criteria['type_tiers'] !== null) {
                $contratSearch->setTypeTiersSearch($criteria['type_tiers']);
            }

            // Définir les dates seulement si elles ne sont pas nulles
            if (isset($criteria['date_enregistrement_debut']) && $criteria['date_enregistrement_debut'] !== null) {
                if (is_string($criteria['date_enregistrement_debut'])) {
                    $contratSearch->setDateEnregistrementDebut(new \DateTime($criteria['date_enregistrement_debut']));
                } else {
                    $contratSearch->setDateEnregistrementDebut($criteria['date_enregistrement_debut']);
                }
            }

            if (isset($criteria['date_enregistrement_fin']) && $criteria['date_enregistrement_fin'] !== null) {
                if (is_string($criteria['date_enregistrement_fin'])) {
                    $contratSearch->setDateEnregistrementFin(new \DateTime($criteria['date_enregistrement_fin']));
                } else {
                    $contratSearch->setDateEnregistrementFin($criteria['date_enregistrement_fin']);
                }
            }

            if (isset($criteria['date_debut_contrat']) && $criteria['date_debut_contrat'] !== null) {
                if (is_string($criteria['date_debut_contrat'])) {
                    $contratSearch->setDateDebutContrat(new \DateTime($criteria['date_debut_contrat']));
                } else {
                    $contratSearch->setDateDebutContrat($criteria['date_debut_contrat']);
                }
            }

            if (isset($criteria['date_fin_contrat']) && $criteria['date_fin_contrat'] !== null) {
                if (is_string($criteria['date_fin_contrat'])) {
                    $contratSearch->setDateFinContrat(new \DateTime($criteria['date_fin_contrat']));
                } else {
                    $contratSearch->setDateFinContrat($criteria['date_fin_contrat']);
                }
            }
        }
    }
}
