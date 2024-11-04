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
        $sql = "SELECT 
        numeroItv 
        from ors_soumis_a_validation
        where numeroOR = '".$numOr."'
        and numeroVersion in (select max(numeroVersion) from ors_soumis_a_validation where numeroOR = '".$numOr."')
        ";
        $exec = $this->connexion->query($sql);
        $tab = [];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return array_column($tab, 'numeroItv');
    }

    public function findItvDejaSoumis($numOr)
    {
        $sql ="SELECT DISTINCT numeroitv AS numeroItv
            FROM ri_soumis_a_validation
            WHERE numero_oR = '".$numOr."'
            ";

        $exec = $this->connexion->query($sql);
        $tab = [];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return array_column($tab, 'numeroItv');
    }

    public function recupInterventionOr($numOr, $itvDejaSoumis)
    {

        if(!empty($itvDejaSoumis)){
            $chaine = implode(",", $itvDejaSoumis);
            $condition = "  and sitv_interv not in (".$chaine.")";
        } else {
            $condition ="";
        }

        $statement = "SELECT 
         sitv_interv as numeroItv, 
         trim(sitv_comment) as commentaire
         from sav_itv
        where sitv_numor = '".$numOr."'
            $condition
            group by 1,2
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function recupNumeroOr($numDit)
    {
        $statement = " SELECT 
            seor_numor as numOr
            from sav_eor
            where seor_refdem = '".$numDit."'
            AND seor_serv = 'SAV'

        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

}

