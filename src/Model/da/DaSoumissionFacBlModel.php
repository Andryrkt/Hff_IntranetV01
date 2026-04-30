<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionFacBlModel extends Model
{
    public function getMontantReceptionIpsEtNumFac(string $numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT  SUM(fllf_pxach) as montant_reception_ips, fliv_livext as numero_facture
                        FROM informix.frn_llf 
                        join informix.frn_liv on fliv_numliv = fllf_numliv 
                        WHERE fllf_numliv='$numeroLivraison' and fliv_soc='$codeSociete'
                        group by numero_facture
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }


    public function getRefDesiFrnCdl($numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT TRIM(fcdl_refp) as reference
                ,TRIM(fcdl_desi) as designation
                ,fllf_numliv as numero_livraison
            from informix.frn_cdl
            left join Informix.frn_llf on fllf_numcde = fcdl_numcde and fcdl_soc='$codeSociete'
            where fllf_numliv = '$numeroLivraison'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getRefDesiSavLor($numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT slor_numcf as numero_livraison 
                ,TRIM(slor_refp) as reference 
                ,TRIM(slor_desi) as designation 
                from Informix.sav_lor 
                where slor_numcf ='$numeroLivraison' and slor_soc='$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
