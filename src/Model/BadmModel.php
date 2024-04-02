<?php

namespace App\Model;

use App\Model\OdbcCrudModel;

class BadmModel extends Model
{
    private $odbcCrud;

    public function __construct()
    {


        parent::__construct();


        $this->odbcCrud = new OdbcCrudModel;
    }


    public function recuperationCaracterMateriel()
    {
        $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT";


        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }
}
