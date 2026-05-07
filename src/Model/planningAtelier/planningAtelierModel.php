<?php

namespace App\Model\planningAtelier;

use App\Model\Model;

class planningAtelierModel extends Model
{
    use PlanningAtelierModelTrait;
    public function recupData($criteria)
    {
        $statement = $this->getStatement($criteria);
        $planningExec = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($planningExec);
        $result = $this->convertirEnUtf8($data);
        return $result;
    }

    public function recupSection()
    {
        $statement = " SELECT  distinct trim(skg_id) as num,
        trim(skg_name) as section
         from skg, sav_itv  
         where skg_soc = 'HF' 
         and skg_succ = sitv_succ 
         and skg_succ = sitv_succ 
         order by num asc
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function recupRessource()
    {
        $statement = " SELECT distinct trim(skr_name) as ressource
                    FROM ska, skr, skr_skg, skg, skw, sav_itv
                    WHERE ska_soc = 'HF'
                    AND sitv_soc = ska.ska_soc 
                    AND sitv_numor = skw.ofh_id 
                    AND sitv_interv = skw.ofs_id 
                    AND ska.skw_id = skw.skw_id
                    AND ska.skr_id = skr.skr_id 
                    AND skr.skr_id = skr_skg.skr_id 
                    AND skr_skg.skr_skg_soc = ska.ska_soc 
                    AND skr_skg.skr_skg_succ = sitv_succ
                    AND skg.skg_succ = sitv_succ
                    AND skg.skg_id = skr_skg.skg_id
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
