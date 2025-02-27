<?php

namespace App\Model\export;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class ExportExcelModel extends Model
{
    use ConversionModel;

    public function recuperationConstructeur()
    {
        $statement = "SELECT 
                        distinct 
                        trim(astp_constp) as constructeur, 
                        trim(astp_refp)  as referencepiece from art_stp where astp_succ = '01' and astp_stock > 1";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function recuperationDonneeConstructeur($referencepiece, $constructeur)
    {
        $statement = "SELECT 
                        first 10 
                        ahis_constp as constructeur,
                        trim(ahis_refp) as referencepiece,
                        ahis_natmouv as natmouv,
                        ahis_qte as qte,
                        ahis_prix as prix,
                        ahis_datemouv as datemouv,
                        ahis_ident as ident,
                        ahis_natop as natop,
                        ahis_nomtiers as nomtiers,
                        ahis_numfac as numfac,
                        ahis_dateop as dateop,
                        ahis_module as module
                    from art_his where ahis_refp = '$referencepiece' and ahis_constp = '$constructeur' and ahis_succ = '01' order by ahis_datemouv desc";
        // dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }
}
