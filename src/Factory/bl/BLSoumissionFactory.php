<?php

namespace App\Factory\bl;

use App\Entity\bl\BLSoumission;

class BLSoumissionFactory
{
    public static function createBLSoumission(string $codeAgenceUser, string $codeServiceUser, string $nomUtilisateur, string $cheminEtNomFichier, int $typeBl): BLSoumission
    {
        $blSoumission = new BLSoumission();
        // Set default values or perform any initialization if needed
        $blSoumission->setAgenceUser($codeAgenceUser);
        $blSoumission->setServiceUser($codeServiceUser);
        $blSoumission->setUtilisateur($nomUtilisateur);
        $blSoumission->setPathFichierSoumis($cheminEtNomFichier);
        $blSoumission->setTypeBl($typeBl === 2 ? BLSoumission::TYPE_BL_INTERNE : BLSoumission::TYPE_FACTURE_BL_CLIENT);

        return $blSoumission;
    }
}
