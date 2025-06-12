<?php

namespace App\Model\dit;


use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DitAutocompleteModel extends Model
{
    use ConversionModel;

    public function recupAllClientExterne()
    {
        $statement = " SELECT cbse_nomcli, cbse_numcli FROM cli_bse , cli_soc WHERE cbse_numcli = csoc_numcli and csoc_soc ='HF'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNomClientExterne($term)
    {
        $statement = " SELECT cbse_nomcli FROM cli_bse, cli_soc WHERE cbse_numcli = csoc_numcli and csoc_soc ='HF' and cbse_nomcli LIKE '%" . $term . "%'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroClientExterne($term)
    {
        $statement = " SELECT cbse_numcli FROM cli_bse , cli_soc WHERE cbse_numcli = csoc_numcli and csoc_soc ='HF' and CAST(cbse_numcli AS CHAR(20)) LIKE '%" . $term . "%'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}
