<?php

namespace App\Controller\Traits\da;

use App\Controller\Controller;
use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DaAfficherRepository;
use App\Service\autres\VersionService;

trait DaNewTrait
{
    use EntityManagerAwareTrait;

    private DaAfficherRepository $daAfficherRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaNewTrait(): void
    {
        $this->daAfficherRepository = $this->getEntityManager()->getRepository(DaAfficher::class);
    }

    /**
     * Ajoute les données d'une Demande d'Achat (et éventuellement d'une Demande d'Intervention)
     * dans la table `DaAfficher`, une ligne par DAL (Demande d'Achat Ligne).
     *
     * @param DemandeAppro $demandeAppro  Objet de la demande d'achat à traiter
     * @param DemandeIntervention|null $dit  Optionnellement, la demande d'intervention associée
     */
    public function ajouterDaDansTableAffichage(DemandeAppro $demandeAppro, ?DemandeIntervention $dit = null): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        foreach ($demandeAppro->getDAL() as $dal) {
            $daAfficher = new DaAfficher();
            if ($dit) {
                $daAfficher->setDit($dit);
            }
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->enregistrerDal($dal);
            $daAfficher->setNumeroVersion($numeroVersion);

            $this->getEntityManager()->persist($daAfficher);
        }
    }
}
