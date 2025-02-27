<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DitListModel extends Model
{
    use ConversionModel;
    
    public function recupNumeroDevis($numDit)
    {
        $statement = "SELECT 
                    CASE 
                        WHEN seor_serv = 'SAV' AND seor_numor = seor_numdev THEN CAST('' AS VARCHAR(255))
                        WHEN seor_serv = 'SAV' AND seor_numor <> seor_numdev THEN CAST(seor_numor AS VARCHAR(255))
                    END AS numDevis
                    FROM sav_eor
                    where seor_refdem = '".$numDit."'
                ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

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

    public function recupItvNumFac($numOr)
    {
        $statement = " SELECT DISTINCT
                        sitv_interv as itv,
                        slor_numfac AS numeroFac
                    FROM
                        sav_itv
                    JOIN
                        sav_lor ON sitv_numor = slor_numor
                        AND sitv_interv = slor_nogrp / 100
                    WHERE
                        sitv_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);

        return $dataUtf8;
    }
}