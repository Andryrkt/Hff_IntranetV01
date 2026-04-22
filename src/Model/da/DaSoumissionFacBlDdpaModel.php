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

    public function getArticleCde(int $numCde)
    {
        $statement = " SELECT 
                TRIM(fcdl_constp) as constructeur 
                ,TRIM(fcdl_ref) as reference
                ,TRIM(fcdl_desi) as designation
                ,ROUND(fcdl_qte) as qte_cde
                ,ROUND(fcdl_solde) as qte_reliquat
                ,ROUND(fcdl_qteli) as  qte_receptionnee
            FROM informix.frn_cdl 
            where fcdl_numcde ='$numCde'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
