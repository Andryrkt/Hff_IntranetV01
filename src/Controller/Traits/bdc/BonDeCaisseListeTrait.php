<?php

namespace App\Controller\Traits\bdc;

use App\Entity\admin\Agence;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;

trait BonDeCaisseListeTrait
{
    private function autorisationRole($em): bool
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->getSessionService()->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $roleIds = $userConnecter->getRoleIds();
        return in_array(1, $roleIds);
        //FIN AUTORISATION
    }

    private function agenceIdAutoriser($em): array
    {
        /** CREATION D'AUTORISATION */
        $userId = $this->getSessionService()->get('user_id');
        $userConnecter = $em->getRepository(User::class)->find($userId);
        $agenceIds = $userConnecter->getAgenceAutoriserIds();
        
        // Get the agency codes instead of IDs
        $agenceCodes = [];
        foreach ($agenceIds as $agenceId) {
            $agence = $em->getRepository(Agence::class)->find($agenceId);
            if ($agence && $agence->getCodeAgence()) {
                $agenceCodes[] = $agence->getCodeAgence();
            }
        }
        
        return $agenceCodes;
        //FIN AUTORISATION
    }

    private function initialisation($bonCaisseSearch, $em)
    {
        $criteria = $this->getSessionService()->get('bon_caisse_search_criteria', []);
        if ($criteria !== null) {
            // Vérifier si statutDemande est un objet ou une chaîne
            if (isset($criteria['statutDemande']) && is_object($criteria['statutDemande'])) {
                $statut = $criteria['statutDemande'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statutDemande']->getId());
            } else {
                $statut = $criteria['statutDemande'] ?? null;
            }
        } else {
            $statut = null;
        }
    
        // Définir le statut s'il n'est pas null
        $bonCaisseSearch->setStatutDemande($statut !== null ? (is_object($statut) ? $statut->getDescription() : $statut) : '');
        
        // Définir le matricule s'il existe
        if (isset($criteria['matricule'])) {
            $bonCaisseSearch->setMatricule($criteria['matricule']);
        }
        
        // Définir la date de demande si elle n'est pas nulle
        if (isset($criteria['dateDemande']) && $criteria['dateDemande'] !== null) {
            $bonCaisseSearch->setDateDemande($criteria['dateDemande']);
        }
    }
}