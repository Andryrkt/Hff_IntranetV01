<?php

namespace App\Controller\Traits\da\demandeDevis;

use DateTime;
use DateTimeZone;
use App\Entity\da\DemandeAppro;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;

trait DaDemandeDevisTrait
{
    use DaTrait;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDemandeDevisTrait(): void
    {
        $this->initDaTrait();
    }
    //=====================================================================================

    public function appliquerStatutDemandeDevisEnCours(DemandeAppro $demandeAppro, string $username)
    {
        $em = $this->getEntityManager();

        $demandeAppro
            ->setStatutDal(DemandeAppro::STATUT_DEMANDE_DEVIS)
            ->setDevisDemande(true)
            ->setDateDemandeDevis(new DateTime('now', new DateTimeZone('Indian/Antananarivo')))
            ->setDevisDemandePar($username)
        ;

        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal(DemandeAppro::STATUT_DEMANDE_DEVIS);
            /** @var DemandeApproLR $demandeApproLR */
            foreach ($demandeApproL->getDemandeApproLR() as $demandeApproLR) {
                $demandeApproLR->setStatutDal(DemandeAppro::STATUT_DEMANDE_DEVIS);
                $em->persist($demandeApproLR);
            }
            $em->persist($demandeApproL);
        }

        $em->persist($demandeAppro);

        $em->flush();
    }
}
