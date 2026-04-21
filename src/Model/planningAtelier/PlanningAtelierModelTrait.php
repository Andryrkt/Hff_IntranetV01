<?php

namespace App\Model\planningAtelier;



trait PlanningAtelierModelTrait
{
    private function agenceEm($criteria)
    {
        if (!empty($criteria->getAgenceEm())) {
            $agenceEm = "AND sitv_succ = '" . $criteria->getAgenceEm() . "'  ";
        } else {
            $agenceEm = "";
        }
        return $agenceEm;
    }
    private function agenceDeb($criteria)
    {
        if (!empty($criteria->getAgenceDebite())) {
            $agenceDeb = "AND sitv_succdeb = '" . $criteria->getAgenceDebite() . "'  ";
        } else {
            $agenceDeb = "";
        }
        return $agenceDeb;
    }
    private function serviceDebite($criteria)
    {
        if (!empty($criteria->getServiceDebite())) {
            $serviceDebite = " AND sitv_servdeb in ('" . implode("','", $criteria->getServiceDebite()) . "')";
        } else {
            $serviceDebite = "";
        }
        return  $serviceDebite;
    }
    private function numOR($criteria)
    {
        if (!empty($criteria->getNumOr())) {
            $numOR = "AND sitv_numor = '" . $criteria->getNumOr() . "'  ";
        } else {
            $numOR = "";
        }
        return $numOR;
    }
    private function ressource($criteria)
    {
        if (!empty($criteria->getResource())) {
            $ressource = "AND skr_name = '" . $criteria->getResource() . "'  ";
        } else {
            $ressource = "";
        }
        return $ressource;
    }
    private function section($criteria)
    {
        if (!empty($criteria->getSection())) {
            $section = "AND skg.skg_id = '" . $criteria->getSection() . "'  ";
        } else {
            $section = "";
        }
        return $section;
    }
    private function dateDebut_Fin($criteria)
    {
        if (!empty($criteria->getDateDebut()) && !empty($criteria->getDateFin())) {
            $dateDeb = "AND ska_d_start  between DATETIME(" . $criteria->getDateDebut()->format("Y-m-d") . " ) YEAR TO DAY AND DATETIME( " . $criteria->getDateFin()->format("Y-m-d") . ") YEAR TO DAY";
        } else {
            $dateDeb = "";
        }
        return $dateDeb;
    }

    private function dateDebut_FinPointage($criteria)
    {
        if (!empty($criteria->getDateDebut()) && !empty($criteria->getDateFin())) {
            $dateDeb = "AND shre_date  between DATETIME(" . $criteria->getDateDebut()->format("Y-m-d") . " ) YEAR TO DAY AND DATETIME( " . $criteria->getDateFin()->format("Y-m-d") . ") YEAR TO DAY";
        } else {
            $dateDeb = "";
        }
        return $dateDeb;
    }

    private function getStatement($criteria)
    {
        $agenceEm = $this->agenceEm($criteria);
        $agenceDeb = $this->agenceDeb($criteria);
        $serviceDeb = $this->serviceDebite($criteria);
        $dateDebut = $this->dateDebut_Fin($criteria);
        $datePointage = $this->dateDebut_FinPointage($criteria);
        $numOR = $this->numOR($criteria);
        $ressource = $this->ressource($criteria);
        $section = $this->section($criteria);
        $statement = "SELECT
                        1							as bloc,
                        trim(skg_name)				as section,
                        trim(sitv_comment)			as intitule,
                        sitv_numor					as numOR,
                        sitv_interv					as itv,
                        skr_name					as ressource,
                        round(ska_duration / 8, 2)	as nbJour,
                        s.ska_d_start				as dateDebut,
                        s.ska_d_end					as dateFin,
                        (select sum(h.shre_qtehre) from Informix.sav_hre h
                        inner join Informix.sav_itv on sitv_numor = h.shre_numor and sitv_soc = h.shre_soc and sitv_succ = h.shre_succ and sitv_interv * 100 = h.shre_nogrp
                        where h.shre_numor = w.ofh_id and h.shre_nogrp = w.ofs_id * 100
                        and cast(h.shre_salarie as char(5)) = s.skr_id
                        and h.shre_date = date(s.ska_d_start)
                    ) as hpointee,
                    (select asuc_num ||'-'||  trim(asuc_lib)
                        from Informix.agr_succ where asuc_num = sav_itv.sitv_succ) as agenceEm
                    from Informix.skw w
                    inner join Informix.ska s on s.skw_id = w.skw_id
                    inner join Informix.sav_itv sav_itv
                        on sitv_numor = ofh_id
                        and sitv_interv = ofs_id
                    inner join Informix.skr_skg skr_skg
                        on skr_skg_soc = s.ska_soc
                        and skr_skg_succ = sitv_succ
                        and skr_skg.skr_id = s.skr_id
                    inner join Informix.skr skr on skr.skr_id = skr_skg.skr_id
                    inner join Informix.skg skg on skg_succ = sitv_succ 
                        and skg.skg_id = skr_skg.skg_id
                    where sitv_soc = ska_soc
                    $agenceDeb
                    $serviceDeb
                    $agenceEm
                    $dateDebut
                    $numOR
                    $ressource
                    $section

                    UNION ALL

                    select
                        2															as bloc,
                        trim(skg_name)												as section,
                        trim(sitv_comment)											as intitule,
                        sitv_numor                                                  as numOR,
                        sitv_interv                                                 as itv,
                        skr_name													as ressource,
                        round(sh.shre_qtehre / 8, 2)								as nbJour,
                        cast(sh.shre_date as datetime year to second)				as dateDebut,
                        cast(sh.shre_date as datetime year to second)				as dateFin,
                        sh.shre_qtehre												as hpointee,
                        (select asuc_num ||'-'||  trim(asuc_lib)
                        from Informix.agr_succ where asuc_num = sav_itv.sitv_succ) as agenceEm
                    from Informix.sav_hre sh
                    inner join Informix.skr skr
                        on skr.skr_id = cast(sh.shre_salarie as char(5))
                    inner join Informix.skr_skg skr_skg
                        on skr_skg.skr_id = skr.skr_id
                        and skr_skg.skr_skg_soc = sh.shre_soc
                        and skr_skg.skr_skg_succ = sh.shre_succ
                    inner join Informix.skg skg
                        on skg.skg_id = skr_skg.skg_id
                    inner join Informix.sav_itv sav_itv
                        on sav_itv.sitv_numor = sh.shre_numor
                        and sav_itv.sitv_succ = sh.shre_succ
                        and sav_itv.sitv_interv = trunc(sh.shre_nogrp / 100)
                    where sitv_soc = sh.shre_soc
                        and not exists (
                            select 1
                            from Informix.ska ka,
                                Informix.skw kw
                            where ka.skw_id = cast(kw.skw_id as integer)
                                and ka.skr_id = skr.skr_id
                                and ka.ska_soc = sh.shre_soc
                                and date(ka.ska_d_start) = sh.shre_date
                                and kw.ofs_id = sav_itv.sitv_interv
                                and kw.ofh_id = sh.shre_numor
                                and kw.skw_soc = sh.shre_soc
                                and kw.skw_succ = sh.shre_succ
                        )
                        $agenceDeb
                        $serviceDeb
                        $agenceEm
                        $datePointage
                        $numOR
                        $ressource
                        $section
                    group by 2, 3, 4, 5, 6, 7, 8, 9, 10, 11
                    order by section, numor, itv, ressource, dateDebut";
        return $statement;
    }
}
