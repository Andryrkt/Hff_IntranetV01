<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;

trait DaListeTrait
{
    use DaTrait;

    /**
     * Met à jour le champ `joursDispo` pour chaque DAL sauf si elle est déjà validée.
     *
     * @param iterable<DemandeApproL> $dalDernieresVersions
     */
    private function ajoutNbrJourRestant($dalDernieresVersions)
    {
        foreach ($dalDernieresVersions as $dal) {
            if ($dal->getStatutDal() != DemandeAppro::STATUT_VALIDE) { // si le statut de la DAL est différent de "Bon d’achats validé" 
                $dal->setJoursDispo($this->getJoursRestants($dal));
            }
        }
    }
}
