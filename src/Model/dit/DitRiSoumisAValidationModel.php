<?php

namespace App\Model\dit;

use App\Controller\Traits\ConversionTrait;
use App\Model\Model;

class DitRiSoumisAValidationModel extends Model
{
    use ConversionTrait;

    public function recupNumeroSoumission($numOr) {
        $sql = "SELECT COALESCE(MAX(numero_soumission)+1, 1) AS numSoumissionEncours
                FROM ri_soumis_a_validation
                WHERE numero_or = '".$numOr."'";
        
        $exec = $this->connexion->query($sql);
        $result = odbc_fetch_array($exec);
        
        return $result['numSoumissionEncours'];
    }

    public function recupNumeroItv($numOr)
    {
        $statement = "SELECT
                    slor_numor AS numeroOr, 
                    slor_nogrp / 100 AS numeroItv
                FROM
                    sav_lor
                JOIN
                    sav_itv ON sitv_numor = slor_numor
                            AND sitv_interv = slor_nogrp / 100
                WHERE
                    sitv_servcrt IN ('ATE', 'FOR', 'GAR', 'MAN', 'CSP', 'MAS')
                    AND slor_numor = '".$numOr."'
                GROUP BY
                numeroOr, numeroItv
                ORDER BY
                    numeroItv
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

}

