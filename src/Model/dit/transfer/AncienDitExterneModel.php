<?php

namespace App\Model\dit\transfer;

use App\Model\Model;

class AncienDitExterneModel extends Model
{
    public function recupDit()
    {
        $sql = " SELECT TOP 3 * 
            from DemandeIntervention 
            where InterneExterne='E'
            and IDStatutInfo <> 15
            and NumeroDemandeIntervention <> ''
        ";

        $execSql = $this->connexion04->query($sql);

        $result = [];
        while ($tab = odbc_fetch_array($execSql)) {
            $result[] = $tab;
        }

        return $result;
    }
}
