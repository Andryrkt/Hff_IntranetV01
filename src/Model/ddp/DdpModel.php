<?php

namespace App\Model\ddp;

use App\Model\Model;

class DdpModel extends Model
{
    public function getModePaiement()
    {
        $statement = " SELECT TRIM(atab_lib) as atablib 
                        from agr_tab 
                        where atab_nom='PAI'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'atablib');
    }

    public function getDevise()
    {
        $statement = " SELECT adev_code as adevcode, 
                            TRIM(adev_lib)as adevlib 
                        from agr_dev
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }
}
