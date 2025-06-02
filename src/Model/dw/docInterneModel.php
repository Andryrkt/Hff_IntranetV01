<?php

namespace App\Model\dw;

use App\Controller\Traits\ConversionTrait;
use App\Model\Model;

class docInterneModel extends Model
{
    public function getDistinctColumn($column)
    {
        $statement = "SELECT DISTINCT $column FROM DW_Processus_procedure";
        $result = $this->connexion->query($statement);
        $data = [];
        while ($tabType = odbc_fetch_array($result)) {
            $data[$tabType[$column]] = $tabType[$column];
        }
        return $data;
    }
}
