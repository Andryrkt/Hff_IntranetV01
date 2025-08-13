<?php

namespace App\Controller\Traits\da\modification;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Repository\da\DaObservationRepository;

trait DaEditDirectTrait
{
    use DaEditTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaEditDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================

    private function filtreDal($demandeAppro, int $numeroVersionMax): DemandeAppro
    {
        // filtre une collection de versions selon le numero de version max
        $dernieresVersions = $demandeAppro->getDAL()->filter(function ($item) use ($numeroVersionMax) {
            return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
        });
        $demandeAppro->setDAL($dernieresVersions); // on remplace la collection de versions par la collection filtrée

        return $demandeAppro;
    }

    public function statutDaModifier(DemandeAppro $demandeAppro): string
    {
        $statutDwAModifier = $demandeAppro->getStatutDal() === DemandeAppro::STATUT_DW_A_MODIFIER;
        return $statutDwAModifier ? DemandeAppro::STATUT_A_VALIDE_DW : DemandeAppro::STATUT_SOUMIS_APPRO;
    }
}
