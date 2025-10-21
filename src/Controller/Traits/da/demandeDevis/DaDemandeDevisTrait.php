<?php

namespace App\Controller\Traits\da\demandeDevis;

use DateTime;
use DateTimeZone;
use App\Entity\da\DemandeAppro;
use App\Controller\Traits\da\DaTrait;

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
        $demandeAppro
            ->setDevisDemande(true)
            ->setDateDemandeDevis(new DateTime('now', new DateTimeZone('Indian/Antananarivo')))
            ->setDevisDemandePar($username)
        ;

        $this->appliquerChangementStatut($demandeAppro, DemandeAppro::STATUT_DEMANDE_DEVIS);
    }
}
