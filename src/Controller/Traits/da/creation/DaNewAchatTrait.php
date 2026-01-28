<?php

namespace App\Controller\Traits\da\creation;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeApproParent;
use App\Entity\da\DemandeApproParentLine;
use App\Service\autres\VersionService;
use App\Traits\JoursOuvrablesTrait;
use Symfony\Component\HttpFoundation\Request;

trait DaNewAchatTrait
{
    use DaNewTrait, JoursOuvrablesTrait;

    //=====================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewAchatTrait(): void
    {
        $this->initDaTrait();
    }
    //=====================================================================================

    /** 
     * Fonction pour initialiser une demande appro direct
     * 
     * @return DemandeApproParent la demande appro initialisée
     */
    private function initialisationDemandeApproAchat(): DemandeApproParent
    {
        $demandeApproParent = new DemandeApproParent();

        $agenceServiceIps = $this->agenceServiceIpsObjet();
        $agence = $agenceServiceIps['agenceIps'];
        $service = $agenceServiceIps['serviceIps'];

        $demandeApproParent
            ->setAgenceDebiteur($agence)
            ->setServiceDebiteur($service)
            ->setAgenceEmetteur($agence)
            ->setServiceEmetteur($service)
            ->setAgenceServiceDebiteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setAgenceServiceEmetteur($agence->getCodeAgence() . '-' . $service->getCodeService())
            ->setUser($this->getUser())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setDateFinSouhaite($this->ajouterJoursOuvrables(5)) // Définit la date de fin souhaitée automatiquement à 5 jours après la date actuelle
        ;

        return $demandeApproParent;
    }

    /** 
     * Fonction pour retourner le nom du bouton cliqué
     *  - enregistrerBrouillon
     *  - soumissionAppro
     */
    private function getButtonName(Request $request): string
    {
        if ($request->request->has('enregistrerBrouillon')) {
            return 'enregistrerBrouillon';
        } elseif ($request->request->has('soumissionAppro')) {
            return 'soumissionAppro';
        } else {
            return '';
        }
    }

    /**
     * Ajoute les données d'une Demande d'Achat (et éventuellement d'une Demande d'Intervention)
     * dans la table `DaAfficher`, une ligne par DAL (Demande d'Achat Ligne).
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->getEntityManager()->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param DemandeApproParent $demandeApproParent  Objet de la demande d'achat à traiter
     * @param bool               $firstCreation       indique si c'est la première création de la DA
     */
    public function ajouterDaDansTableAffichageParent(DemandeApproParent $demandeApproParent, bool $firstCreation): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $firstCreation ? 0 : $this->daAfficherRepository->getNumeroVersionMax($demandeApproParent->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        /** @var DemandeApproParentLine $dal */
        foreach ($demandeApproParent->getDemandeApproParentLines() as $demandeApproParentLine) {
            $daAfficher = new DaAfficher();
            $daAfficher->duplicateDaParent($demandeApproParent);
            $daAfficher->duplicateDaParentLine($demandeApproParentLine);
            $daAfficher->setNumeroVersion($numeroVersion);

            $this->getEntityManager()->persist($daAfficher);
        }
        $this->getEntityManager()->flush();
    }
}
