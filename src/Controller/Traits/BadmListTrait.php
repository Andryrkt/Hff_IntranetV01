<?php

namespace App\Controller\Traits;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
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

    private function agenceServiceEmetteur(bool $autoriser): array
    {
        //initialisation agence et service
        if ($autoriser) {
            $agence = null;
            $service = null;
        } else {
            $userInfo = $this->getSessionService()->get('user_info');
            $agence = $userInfo['authorized_agences']['ids'] ?? null;
            $service = null;
        }

        return [
            'agence' => $agence,
            'service' => $service
        ];
    }

    private function agenceServiceIpsEmetteur($autoriser, $agenceServiceIps)
    {
        if ($autoriser) {
            $agenceIpsEmetteur = null;
            $ServiceIpsEmetteur = null;
        } else {
            $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
            $ServiceIpsEmetteur = $agenceServiceIps['serviceIps'];
        }

        return [
            'agenceIpsEmetteur' => $agenceIpsEmetteur,
            'serviceIpsEmetteur' => $ServiceIpsEmetteur
        ];
    }

    private function initialisation($badmSearch, $em, $agenceServiceIps, $autoriser)
    {
        $criteria = $this->getSessionService()->get('badm_search_criteria', []);
        $agenceServiceIpsEmetteur = $this->agenceServiceIpsEmetteur($autoriser, $agenceServiceIps);
        if (!empty($criteria)) {
            $typeMouvement = $criteria['typeMouvement'] === null ? null : $em->getRepository(TypeMouvement::class)->find($criteria['typeMouvement']->getId());
            $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
            // $serviceEmetteur = $criteria['serviceEmetteur'] === null ? $agenceServiceIpsEmetteur['serviceIpsEmetteur'] : $em->getRepository(Service::class)->find($criteria['serviceEmetteur']->getId());
            $serviceEmetteur = $criteria['serviceEmetteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceEmetteur']->getId());
            $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceDebiteur']->getId());
            $agenceEmetteur = $criteria['agenceEmetteur'] === null ? $agenceServiceIpsEmetteur['agenceIpsEmetteur'] : $em->getRepository(Agence::class)->find($criteria['agenceEmetteur']->getId());
            $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $em->getRepository(Agence::class)->find($criteria['agenceDebiteur']->getId());
        } else {
            $typeMouvement = null;
            $statut = null;
            $serviceEmetteur = null;
            $serviceDebiteur = null;
            $agenceEmetteur = null;
            $agenceDebiteur = null;
        }

        $badmSearch
            ->setStatut($statut)
            ->setTypeMouvement($typeMouvement)
            ->setDateDebut($criteria['dateDebut'] ?? null)
            ->setDateFin($criteria['dateFin'] ?? null)
            ->setIdMateriel($criteria['idMateriel'] ?? null)
            ->setAgenceEmetteur($agenceEmetteur)
            ->setServiceEmetteur($serviceEmetteur)
            ->setAgenceDebiteur($agenceDebiteur)
            ->setServiceDebiteur($serviceDebiteur)
        ;
    }
}
