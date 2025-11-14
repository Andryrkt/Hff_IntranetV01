<?php

namespace App\Controller\Traits\ddc;

use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;

trait CongeListeTrait
{

    private function initialisation($congeSearch, $em)
    {
        $criteria = $this->getSessionService()->get('conge_search_criteria', []);
        if ($criteria !== null) {
            // Vérifier si sousTypeDocument est un objet ou une chaîne
            if (isset($criteria['sousTypeDocument']) && is_object($criteria['sousTypeDocument'])) {
                $sousTypeDocument = $criteria['sousTypeDocument'] === null ? null : $em->getRepository(SousTypeDocument::class)->find($criteria['sousTypeDocument']->getId());
            } else {
                $sousTypeDocument = $criteria['sousTypeDocument'] ?? null;
            }

            // Vérifier si statutDemande est un objet ou une chaîne
            if (isset($criteria['statutDemande']) && is_object($criteria['statutDemande'])) {
                $statut = $criteria['statutDemande'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statutDemande']->getId());
            } else {
                $statut = $criteria['statutDemande'] ?? null;
            }
        } else {
            $sousTypeDocument = null;
            $statut = null;
        }

        // Définir le statut et le sous-type de document s'ils ne sont pas null
        $congeSearch->setStatutDemande($statut !== null ? (is_object($statut) ? $statut->getDescription() : $statut) : '');
        $congeSearch->setSousTypeDocument($sousTypeDocument !== null ? (is_object($sousTypeDocument) ? $sousTypeDocument->getDescription() : $sousTypeDocument) : '');

        // Définir le matricule s'il existe
        if (isset($criteria['matricule'])) {
            $congeSearch->setMatricule($criteria['matricule']);
        }

        // Définir les dates seulement si elles ne sont pas nulles
        if (isset($criteria['dateDebut']) && $criteria['dateDebut'] !== null) {
            $congeSearch->setDateDebut($criteria['dateDebut']);
        }

        if (isset($criteria['dateFin']) && $criteria['dateFin'] !== null) {
            $congeSearch->setDateFin($criteria['dateFin']);
        }
    }
}
