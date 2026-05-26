<?php

namespace App\Controller\Traits\da\demandeDevis;

use App\Constants\da\StatutDaConstant;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeAppro;
use DateTime;
use DateTimeZone;

trait DaDemandeDevisTrait
{
    use DaTrait;

    public function initDaDemandeDevisTrait()
    {
        $this->initDaTrait();
    }

    public function appliquerStatutDemandeDevisEnCours(DemandeAppro $demandeAppro, string $username)
    {
        $demandeAppro
            ->setDevisDemande(true)
            ->setDateDemandeDevis(new DateTime('now', new DateTimeZone('Indian/Antananarivo')))
            ->setDevisDemandePar($username)
        ;

        $this->appliquerChangementStatut($demandeAppro, StatutDaConstant::STATUT_DEMANDE_DEVIS);
    }
}
