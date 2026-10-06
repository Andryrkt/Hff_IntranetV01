<?php

namespace App\Controller\Traits;

use App\Entity\admin\StatutDemande;
use App\Entity\admin\badm\TypeMouvement;

trait BadmListTrait
{
    private function recuperationCriterie($badmSearch, $form)
    {
        $badmSearch->setStatut($form->get('statut')->getData());
        $badmSearch->setTypeMouvement($form->get('typeMouvement')->getData());
        $badmSearch->setDateDebut($form->get('dateDebut')->getData());
        $badmSearch->setDateFin($form->get('dateFin')->getData());
        $badmSearch->setAgenceEmetteur($form->get('agenceEmetteur')->getData());
        $badmSearch->setServiceEmetteur($form->get('serviceEmetteur')->getData());
        $badmSearch->setAgenceDebiteur($form->get('agenceDebiteur')->getData());
        $badmSearch->setServiceDebiteur($form->get('serviceDebiteur')->getData());
    }

    private function initialisation($badmSearch, $em)
    {
        $criteria = $this->getSessionService()->get('badm_search_criteria', []);
        if (!empty($criteria)) {
            $typeMouvement = $criteria['typeMouvement'] === null ? null : $em->getRepository(TypeMouvement::class)->find($criteria['typeMouvement']->getId());
            $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
        } else {
            $typeMouvement = null;
            $statut = null;
        }

        $badmSearch
            ->setStatut($statut)
            ->setTypeMouvement($typeMouvement)
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setIdMateriel($criteria['idMateriel'] ?? null)
            ->setAgenceEmetteur($criteria['agenceEmetteur'] ?? null)
            ->setServiceEmetteur($criteria['serviceEmetteur'] ?? null)
            ->setAgenceDebiteur($criteria['serviceDebiteur'] ?? null)
            ->setServiceDebiteur($criteria['agenceDebiteur'] ?? null)
        ;
    }
}
