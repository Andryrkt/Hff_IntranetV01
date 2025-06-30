<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionBcModel extends Model
{
    public function getNumDa(string $numCde)
    {
        $statement = " SELECT fc.fcde_cdeext as num_da 
                        from informix.frn_cde fc
                        where fcde_soc = 'HF' 
                        and fcde_numcde = '$numCde'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'num_da');
    }
}
