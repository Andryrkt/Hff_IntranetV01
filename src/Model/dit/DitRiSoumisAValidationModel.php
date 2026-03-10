<?php

namespace App\Model\dit;

use App\Controller\Traits\ConversionTrait;
use App\Model\Model;

class DitRiSoumisAValidationModel extends Model
{
    use ConversionTrait;

    public function recupNumeroSoumission($numOr, string $codeSociete)
    {
        $sql = "SELECT COALESCE(MAX(numero_soumission)+1, 1) AS numSoumissionEncours
                FROM ri_soumis_a_validation
                WHERE numero_or = '$numOr' and code_societe = '$codeSociete'";

        $exec = $this->connexion->query($sql);
        $result = odbc_fetch_array($exec);

        return $result['numSoumissionEncours'];
    }

    public function recupNumeroItv($numOr, $codeSociete)
    {
        $sql = "SELECT numeroItv 
        from ors_soumis_a_validation
        where numeroOR = '$numOr'
        and code_societe = '$codeSociete'
        and numeroVersion in (select max(numeroVersion) from ors_soumis_a_validation where numeroOR = '$numOr' and code_societe = '$codeSociete')
        ";
        $exec = $this->connexion->query($sql);
        $tab = [];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return array_column($tab, 'numeroItv');
    }

    public function findItvDejaSoumis($numOr, $codeSociete)
    {
        $sql = "SELECT DISTINCT numeroitv AS numeroItv
            FROM ri_soumis_a_validation
            WHERE numero_oR = '$numOr' AND code_societe = '$codeSociete'
            ";

        $exec = $this->connexion->query($sql);
        $tab = [];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return array_column($tab, 'numeroItv');
    }

    public function recupInterventionOr($numOr, $itvDejaSoumis, $codeSociete)
    {
        if (!empty($itvDejaSoumis)) {
            $chaine = implode(",", $itvDejaSoumis);
            $condition = "  and sitv_interv not in (" . $chaine . ")";
        } else {
            $condition = "";
        }

        $statement = "SELECT 
         sitv_interv as numeroItv, 
         trim(sitv_comment) as commentaire
         from sav_itv
        where sitv_numor = '$numOr' and sitv_soc = '$codeSociete'
            $condition
            group by 1,2
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function recupNumeroOr($numDit, $codeSociete)
    {
        $statement = " SELECT 
            seor_numor as numOr
            from sav_eor
            where seor_refdem = '$numDit'
            AND seor_serv = 'SAV'
            AND seor_soc = '$codeSociete'
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}
