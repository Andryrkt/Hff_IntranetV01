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
}

