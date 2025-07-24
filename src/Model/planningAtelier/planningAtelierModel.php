<?php

namespace App\Model\planningAtelier;

use App\Model\Model;

class planningAtelierModel extends Model
{
    use PlanningAtelierModelTrait;
    public function recupData($criteria)
    {
        $agenceEm = $this->agenceEm($criteria);
        $agenceDeb = $this->agenceDeb($criteria);
        $serviceDeb = $this->serviceDebite($criteria);
        $dateDebut = $this->dateDebut_Fin($criteria);
        $numOR = $this->numOR($criteria);
        $ressource = $this->ressource($criteria);
        $section = $this->section($criteria);
        $statement = "SELECT  distinct ska_id as id ,
                                    trim(skg_name) as section, 
                                    trim(sitv_comment) as intitule,
                                    sitv_numor as numOR,
                                    sitv_interv as itv,
                                    trim(skr_name) as ressource,
                                    round(ska_duration)/8 as nbJour,
                                    ska.ska_d_start as dateDebut,
                                    ska.ska_d_end as dateFin
                    from ska, skr, skr_skg, skg, skw, sav_itv
                    where ska_soc = 'HF'
                    and sitv_soc = ska.ska_soc and sitv_numor = skw.ofh_id and sitv_interv = skw.ofs_id and ska.skw_id = skw.skw_id
                    and ska.skr_id = skr.skr_id and skr.skr_id = skr_skg.skr_id and skr_skg.skr_skg_soc = ska.ska_soc and skr_skg.skr_skg_succ = sitv_succ
                    and skg.skg_succ = sitv_succ
                    and skg.skg_id = skr_skg.skg_id
                    $agenceDeb
                    $serviceDeb
                    $agenceEm
                    $dateDebut
                    $numOR
                    $ressource
                    $section

                    order by   section,numOR,itv,ressource
        ";
        // dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
