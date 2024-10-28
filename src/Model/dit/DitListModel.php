<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DitListModel extends Model
{
    use ConversionModel;
    
    public function recupItvComment($numOr)
    {
        $statement = " SELECT 
                    TRUNC(slor_nogrp / 100) as numeroItv, 
                    trim(sitv_comment) as commentaire
                    from sav_lor
                    inner join sav_itv on sitv_numor = slor_numor and sitv_interv = slor_nogrp / 100 and slor_soc = 'HF'
                    where slor_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbItv($numOr)
    {
        $statement = " SELECT 
                    COUNT(slor_nogrp / 100) as nbItv
                    from sav_lor
                    where slor_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);

        // Vérifier si des données existent et retourner la première valeur de 'nbItv'
        return !empty($dataUtf8) ? $dataUtf8[0]['nbitv'] : null;
    }
}