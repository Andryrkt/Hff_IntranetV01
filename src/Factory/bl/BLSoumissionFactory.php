<?php

namespace App\Factory\bl;

use App\Entity\bl\BLSoumission;
use App\Entity\admin\utilisateur\User;


class BLSoumissionFactory
{
    public static function createBLSoumission(User $user, string $cheminEtNomFichier): BLSoumission
    {
        dd($user);
        $blSoumission = new BLSoumission();
        // Set default values or perform any initialization if needed
        // $blSoumission->setAgenceUser($user->);
        // $blSoumission->setServiceUser($dto->serviceUser);
        // $blSoumission->setUtilisateur($dto->utilisateur);
        $blSoumission->setPathFichierSoumis($cheminEtNomFichier);

        return $blSoumission;
    }
}
