<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitSearch;
use Symfony\Component\HttpFoundation\Request;

trait DemandeApproTrait
{
    private function initialisationDemandeAppro(DemandeAppro $demandeAppro, DemandeIntervention $dit)
    {
        $demandeAppro
            ->setDit($dit)
            ->setNumeroDemandeDit($dit->getNumeroDemandeIntervention())
            ->setAgenceDebiteur($dit->getAgenceDebiteurId())
            ->setServiceDebiteur($dit->getServiceDebiteurId())
            ->setAgenceEmetteur($dit->getAgenceEmetteurId())
            ->setServiceEmetteur($dit->getServiceEmetteurId())
            ->setAgenceServiceDebiteur($dit->getAgenceDebiteurId()->getCodeAgence() . '-' . $dit->getServiceDebiteurId()->getCodeService())
            ->setAgenceServiceEmetteur($dit->getAgenceEmetteurId()->getCodeAgence() . '-' . $dit->getServiceEmetteurId()->getCodeService())
            ->setStatutDal('Ouvert')
        ;
    }
}
