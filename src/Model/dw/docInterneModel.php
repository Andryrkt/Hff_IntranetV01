<?php

namespace App\Model\dw;

use App\Controller\Traits\ConversionTrait;
use App\Model\Model;

class docInterneModel extends Model
{
    use ConversionTrait;

    public function getDistinctColumn($column)
    {
        $statement = "SELECT DISTINCT $column FROM DW_Processus_procedure where $column is not null";
        $result = $this->connexion->query($statement);
        $data = [];
        while ($resultStmt = odbc_fetch_array($result)) {
            $tabType = $this->convertirEnUtf8($resultStmt);
            $data[$tabType[$column]] = $tabType[$column];
        }
        return $data;
    }
}
