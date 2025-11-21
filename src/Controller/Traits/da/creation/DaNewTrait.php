<?php


namespace App\Controller\Traits\da\creation;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Service\autres\VersionService;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeApproL;

trait DaNewTrait
{
    use DaTrait;

    /**
     * Ajoute les données d'une Demande d'Achat (et éventuellement d'une Demande d'Intervention)
     * dans la table `DaAfficher`, une ligne par DAL (Demande d'Achat Ligne).
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->getEntityManager()->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
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
        /** @var DemandeApproL $dal */
        foreach ($demandeAppro->getDAL() as $dal) {
            $daAfficher = new DaAfficher();
            if ($dit) $daAfficher->setDit($dit);
            $daAfficher->setStatutOr(''); // définir à vide
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->enregistrerDal($dal);
            $daAfficher->setNumeroVersion($numeroVersion);

            $this->getEntityManager()->persist($daAfficher);
        }
        $this->getEntityManager()->flush();
    }
}
