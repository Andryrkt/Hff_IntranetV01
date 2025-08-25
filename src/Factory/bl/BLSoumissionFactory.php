<?php

namespace App\Factory\bl;

use App\Entity\bl\BLSoumission;
use App\Entity\admin\utilisateur\User;


class BLSoumissionFactory
{
    public static function createBLSoumission(User $user, string $cheminEtNomFichier): BLSoumission
    {
        $blSoumission = new BLSoumission();
        // Set default values or perform any initialization if needed
        $blSoumission->setAgenceUser($user->getCodeAgenceUser());
        $blSoumission->setServiceUser($user->getCodeServiceUser());
        $blSoumission->setUtilisateur($user->getNomUtilisateur());
        $blSoumission->setPathFichierSoumis($cheminEtNomFichier);

        return $blSoumission;
    }
}
