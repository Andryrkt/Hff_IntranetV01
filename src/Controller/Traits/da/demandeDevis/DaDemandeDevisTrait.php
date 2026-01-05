<?php

namespace App\Controller\Traits\da\demandeDevis;

use DateTime;
use DateTimeZone;
use App\Entity\da\DemandeAppro;

trait DaDemandeDevisTrait
{
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
