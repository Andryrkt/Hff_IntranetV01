<?php

namespace App\Controller\Traits\dom;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;

trait DomListeTrait
{

    private function autorisationRole($em): bool
{
    /** CREATION D'AUTORISATION */
    $userId = $this->sessionService->get('user_id');
    $userConnecter = $em->getRepository(User::class)->find($userId);
    $roleIds = $userConnecter->getRoleIds();
    return in_array(1, $roleIds);
    //FIN AUTORISATION
}
    private function initialisation($badmSearch, $em, $agenceServiceIps, $autoriser)
{
    $criteria = $this->sessionService->get('dom_search_criteria', []);

    if($criteria !== null){
        if($autoriser){
            $agenceIpsEmetteur = null;
            $ServiceIpsEmetteur = null;
        } else {
            $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
            $ServiceIpsEmetteur = $agenceServiceIps['serviceIps'];
        }
        $sousTypeDocument = $criteria['sousTypeDocument'] === null ? null : $em->getRepository(SousTypeDocument::class)->find($criteria['typeMouvement']->getId());
        $statut = $criteria['statut'] === null ? null : $em->getRepository(StatutDemande::class)->find($criteria['statut']->getId());
        $serviceEmetteur = $criteria['serviceEmetteur'] === null ? $ServiceIpsEmetteur : $em->getRepository(Service::class)->find($criteria['serviceEmetteur']->getId());
        $serviceDebiteur = $criteria['serviceDebiteur'] === null ? null : $em->getRepository(Service::class)->find($criteria['serviceDebiteur']->getId());
        $agenceEmetteur = $criteria['agenceEmetteur'] === null ? $agenceIpsEmetteur : $em->getRepository(Agence::class)->find($criteria['agenceEmetteur']->getId());
        $agenceDebiteur = $criteria['agenceDebiteur'] === null ? null : $em->getRepository(Agence::class)->find($criteria['agenceDebiteur']->getId());
    } else {
        if($autoriser){
            $agenceIpsEmetteur = null;
            $ServiceIpsEmetteur = null;
        } else {
            $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
            $ServiceIpsEmetteur = $agenceServiceIps['serviceIps'];
        }
        $sousTypeDocument = null;
        $statut = null;
        $serviceEmetteur = $ServiceIpsEmetteur;
        $serviceDebiteur = null;
        $agenceEmetteur = $agenceIpsEmetteur;
        $agenceDebiteur = null;
    }
   
    $badmSearch
        ->setStatut($statut)
        ->setSousTypeDocument($sousTypeDocument)
        ->setDateDebut($criteria['dateDebut'] ?? null)
        ->setDateFin($criteria['dateFin'] ?? null)
        ->setDateMissionDebut($criteria['dateMissionDebut'] ?? null)
        ->setDateMissionFin($criteria['dateMissionFin'] ?? null)
        ->setMatricule($criteria['matricule'] ?? null)
        ->setAgenceEmetteur($agenceEmetteur)
        ->setServiceEmetteur($serviceEmetteur)
        ->setAgenceDebiteur($agenceDebiteur)
        ->setServiceDebiteur($serviceDebiteur)
    ;
}

private function agenceServiceEmetteur(bool $autoriser): array
{
    $agenceServiceIps= $this->agenceServiceIpsObjet();
        //initialisation agence et service
        if($autoriser){
            $agence = null;
            $service = null;
        } else {
            $agence = $agenceServiceIps['agenceIps'];
            $service = $agenceServiceIps['serviceIps'];
        }

        return [
            'agence' => $agence,
            'service' => $service
        ];
}
}