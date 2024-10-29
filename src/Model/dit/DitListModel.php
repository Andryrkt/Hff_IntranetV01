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
                        sitv_interv as numeroItv,
                        TRIM(sitv_comment) as commentair
                    from sav_itv
                    where sitv_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbItv($numOr)
    {
        $statement = " SELECT 
                    COUNT(sitv_interv) as nbItv
                    from sav_itv
                    where sitv_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);

        // Vérifier si des données existent et retourner la première valeur de 'nbItv'
        return !empty($dataUtf8) ? $dataUtf8[0]['nbitv'] : null;
    }

    public function recupItv($numOr)
    {
        $statement = " SELECT 
                    sitv_interv as itv
                    from sav_itv
                    where sitv_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);

        return array_column($dataUtf8, 'itv');
    }
}