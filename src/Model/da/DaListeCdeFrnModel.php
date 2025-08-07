<?php

namespace App\Model\da;

use App\Model\Model;

class DaListeCdeFrnModel extends Model
{

    public function getNumOrValideZst(string $numOrString)
    {
        $statement = " SELECT DISTINCT slor_numor as num_or
                    from Informix.sav_lor 
                    where slor_constp in ('ZST','ZDI') 
                    and slor_numor in ($numOrString)
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'num_or');
    }
}
