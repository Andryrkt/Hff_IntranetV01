<?php

namespace App\Controller\Traits\dom;

use App\Entity\admin\StatutDemande;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\dom\Dom;

trait DomListeTrait
{
    use DomsTrait;

    private function initialisation($badmSearch, $em)
    {
        $criteria = $this->getSessionService()->get('dom_search_criteria', []);
        if (!empty($criteria)) {
            $sousTypeDocument = $criteria['sousTypeDocument'] === null ? null : $em->getRepository(SousTypeDocument::class)->find($criteria['sousTypeDocument']->getId());
            $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
        } else {
            $sousTypeDocument = null;
            $statut = null;
        }

        $badmSearch
            ->setStatut($statut)
            ->setSousTypeDocument($sousTypeDocument)
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setDateMissionDebut($criteria['dateMissionDebut'] ?? null)
            ->setDateMissionFin($criteria['dateMissionFin'] ?? null)
            ->setMatricule($criteria['matricule'] ?? null)
        ;
    }

    /** 
     * Fonction pour voir si le statut du dom peut être trop perçu ou non
     */
    private function statutTropPercuDomList(array $data)
    {
        /** @var Dom $dom chaque Dom dans $data */
        foreach ($data as $dom) {
            $this->statutTropPercu($dom);
        }
    }
}
