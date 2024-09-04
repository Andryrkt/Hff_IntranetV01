<?php

namespace App\Controller\Traits;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
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

private function autorisationRole($em): bool
{
    /** CREATION D'AUTORISATION */
    $userId = $this->sessionService->get('user_id');
    $userConnecter = $em->getRepository(User::class)->find($userId);
    $roleIds = $userConnecter->getRoleIds();
    return in_array(1, $roleIds);
    //FIN AUTORISATION
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

private function initialisation($badmSearch, $em, $agenceServiceIps, $autoriser)
{
    $criteria = $this->sessionService->get('badm_search_criteria', []);

    if($criteria !== null){
        if($autoriser){
            $agenceIpsEmetteur = null;
            $ServiceIpsEmetteur = null;
        } else {
            $agenceIpsEmetteur = $agenceServiceIps['agenceIps'];
            $ServiceIpsEmetteur = $agenceServiceIps['serviceIps'];
        }
        $typeMouvement = $criteria['typeMouvement'] === null ? null : $em->getRepository(TypeMouvement::class)->find($criteria['typeMouvement']->getId());
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
        $typeMouvement = null;
        $statut = null;
        $serviceEmetteur = $ServiceIpsEmetteur;
        $serviceDebiteur = null;
        $agenceEmetteur = $agenceIpsEmetteur;
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
