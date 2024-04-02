<?php

namespace App\Model;

use App\Model\Model;

class PersonnelModel extends Model
{

    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }
}
