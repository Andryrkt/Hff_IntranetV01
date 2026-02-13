<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionFacBlDdpaModel extends Model
{
    public function getTotalMontantCommande(int $numCde)
    {
        $statement = " SELECT fcde_mtn as montant_total
            from informix.frn_cde 
            where fcde_numcde = $numCde
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_total');
    }

    
}
