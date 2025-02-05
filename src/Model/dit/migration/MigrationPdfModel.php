<?php

namespace App\Model\dit\migration;

class MigrationModel
{
    public function getDitMigrer()
    {
        $sql = " SELECT * FROM demande_intervention
        
        ";        
        $result = [];
        while ($tab = odbc_fetch_array($sql)) {
            $result[] = $tab;
        }

        return $result;
    }
}