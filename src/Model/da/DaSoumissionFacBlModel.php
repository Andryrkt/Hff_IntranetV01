<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionFacBlModel extends Model
{
    public function getMontantReceptionIpsEtNumFac(string $numeroLivraison)
    {
        $statement = " SELECT  SUM(fllf_pxach) as montant_reception_ips,
                		        fliv_livext as numero_facture
                        FROM informix.frn_llf 
                        join
                        	informix.frn_liv on fliv_numliv = fllf_numliv 
                        WHERE fllf_numliv=$numeroLivraison
                        group by numero_facture
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
