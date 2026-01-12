<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionFacBlModel extends Model
{
    public function getMontantReceptionIps(string $numeroLivraison)
    {
        $statement = " SELECT  SUM(fllf_pxach) as montant_reception_ips 
                        FROM informix.frn_llf 
                        WHERE fllf_numliv=$numeroLivraison
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_reception_ips');
    }
}
