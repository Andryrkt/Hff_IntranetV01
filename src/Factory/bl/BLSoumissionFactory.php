<?php

namespace App\Factory\bl;

use App\Entity\bl\BLSoumission;
use App\Entity\admin\utilisateur\User;


class BLSoumissionFactory
{
    public static function createBLSoumission(User $user, string $cheminEtNomFichier, int $typeBl): BLSoumission
    {
        $blSoumission = new BLSoumission();
        // Set default values or perform any initialization if needed
        $blSoumission->setAgenceUser($user->getCodeAgenceUser());
        $blSoumission->setServiceUser($user->getCodeServiceUser());
        $blSoumission->setUtilisateur($user->getNomUtilisateur());
        $blSoumission->setPathFichierSoumis($cheminEtNomFichier);
        $blSoumission->setTypeBl($typeBl === 2 ? BLSoumission::TYPE_BL_INTERNE : BLSoumission::TYPE_FACTURE_BL_CLIENT);

        return $blSoumission;
    }
}
