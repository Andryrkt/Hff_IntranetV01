<?php

namespace App\Model\magasin\lcfnp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Model\Traits\ConditionModelTrait;

class ListeCdeFrnNonPlacerModel extends Model
{
    public function fournisseurIrum() 
    {
        $statement = "SELECT 
                    DISTINCT  fcde_numfou as codeFrs, fbse_nomfou as libFrs
                    FROM frn_cde , frn_bse 
                    WHERE frn_cde .fcde_numfou = frn_bse.fbse_numfou
                    AND  frn_cde .fcde_soc = 'HF'
                    AND fcde_numfou not in ('1','10','20','30','40','50','60','92','10019','6000001')
        ";
         $result = $this->connect->executeQuery($statement);
         $data = $this->connect->fetchResults($result);
         return $this->convertirEnUtf8($data);
    }
}