<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\da\DaTrait;

trait DaEditTrait
{
    use DaTrait;

    /** 
     * Fonction pour obtenir les anciens DAL
     */
    private function getAncienDAL(DemandeAppro $demandeAppro): array
    {
        $result = [];
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $result[] = clone $demandeApproL;
        }
        return $result;
    }

    /**
     * Suppression physique des DALR correspondant au DAL $dal
     *
     * @param DemandeApproL $dal
     * @return void
     */
    private function deleteDALR(DemandeApproL $dal)
    {
        $em = $this->getEntityManager();
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigne' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $em->remove($dalr);
        }
    }

    private function peutModifier(string $statutDa, bool $profil)
    {
        $statutModifiable = in_array($statutDa, [DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_AUTORISER_EMETTEUR]);
        return $statutModifiable && $profil;
    }
}
