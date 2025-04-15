<?php

namespace App\Model\inventaire;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;

class DetailInventaireModel extends Model
{

    public function recuperationAgenceIrium()
    {
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '02' 
                      or ASUC_NUM like '10'
                       or ASUC_NUM like '20'
                       or ASUC_NUM like '30'
                       or ASUC_NUM like '40'
                       or ASUC_NUM like '50'
                       or ASUC_NUM like '60'
                       or ASUC_NUM like '92'
                       
                       )
                      order by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return
            array_map(function ($item) {
                return [$item['asuc_num'] . '-' . $item['asuc_lib'] => $item['asuc_num']];
            }, $dataUtf8);
    }
}
