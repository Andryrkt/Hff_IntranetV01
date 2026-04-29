<?php

namespace App\Model\planningAtelier;

trait PlanningAtelierModelTrait
{
    /**
     * Check`sitv_succ`
     * @param $criteria
     * @return string
     */
    private function agenceEm($criteria, string $table = 'sitv_succ'): string
    {
        $hasAgenceEm = !empty($criteria->getAgenceEm());
        return $hasAgenceEm ? "and ". $table ." = '" . $criteria->getAgenceEm() . "'" : "";
    }
    /**
     * Check`sitv_succdeb`
     * @param $criteria
     * @return string
     */
    private function agenceDeb($criteria, string $table = 'sitv_succdeb'): string
    {
        $hasAgenceDeb = !empty($criteria->getAgenceDebite());
        return $hasAgenceDeb ? "and ". $table ." = '" . $criteria->getAgenceDebite() . "'" : "";
    }
    /**
     * Check`sitv_servdeb`
     * @param $criteria
     * @return string
     */
    private function serviceDebite($criteria, string $table = 'sitv_servdeb'): string
    {
        $hasServiceDebite = !empty($criteria->getServiceDebite());
        return $hasServiceDebite ?
            "and ". $table ." in ('" . implode("','", $criteria->getServiceDebite()) . "')" : "";
    }
    /**
     * Check`sitv_numor`
     * @param $criteria
     * @return string
     */
    private function numOR($criteria, string $table = 'sitv_numor')
    {
        $hasNumOR = !empty($criteria->getNumOr());
        return $hasNumOR ? "and ". $table ." = '" . $criteria->getNumOR() . "'" : "";
    }
    /**
     * Check`skr_name`
     * @param $criteria
     * @return string
     */
    private function ressource($criteria)
    {
        $hasRessource = !empty($criteria->getResource());
        return $hasRessource ? "and skr_name = '" . $criteria->getResource() . "'" : "";
    }
    /**
     * Check `skg.skg_id`
     * @param $criteria
     * @return string
     */
    private function section($criteria)
    {
        $hasSection = !empty($criteria->getSection());
        return $hasSection ? "and skg.skg_id = '" . $criteria->getSection() . "'" : "";
    }
    /**
     * Check `ska_d_start` between date range
     * @param $criteria
     * @return string
     */
    private function dateRange($criteria, string $table = 'ska_d_start')
    {
        $hasDateDebut = !empty($criteria->getDateDebut());
        $hasDateFin = !empty($criteria->getDateFin());
        if (!$hasDateDebut || !$hasDateFin)
            return "";
        $startDate = $criteria->getDateDebut()->format("Y-m-d");
        $endDate = $criteria->getDateFin()->format("Y-m-d");
        return "and ". $table ." between datetime(" . $startDate . ") year to day and datetime(" . $endDate . ") year to day";
    }

    private function sqlStatement($criteria)
    {
        return " SELECT DISTINCT
            trim(skg_name)              as section,
            trim(sitv_comment)          as intitule,
            sitv_numor                  as numOR,
            sitv_interv                 as itv,
            skr_name                    as ressource,
            round(ska_duration / 8, 2)  as nbJour,
            s.ska_d_start               as date_debut,
            s.ska_d_end                 as date_fin,
            shre.hpointee,
            shre.hpointee_debut,
            shre.hpointee_fin,
            agr_succ.asuc_num ||'-'|| trim(agr_succ.asuc_lib) as agenceEm
        from Informix.skw w
        inner join Informix.ska s on s.skw_id = w.skw_id
            {$this->dateRange($criteria)}
        inner join Informix.sav_itv sav_itv
            on sitv_numor = ofh_id
            and sitv_interv = ofs_id
            and sitv_soc = s.ska_soc
            {$this->agenceEm($criteria)}
            {$this->agenceDeb($criteria)}
            {$this->serviceDebite($criteria)}
            {$this->numOR($criteria)}
        inner join Informix.skr_skg skr_skg
            on skr_skg_soc = s.ska_soc
            and skr_skg_succ = sitv_succ
            and skr_skg.skr_id = s.skr_id
        inner join Informix.skr skr on skr.skr_id = skr_skg.skr_id
            {$this->ressource($criteria)}
        inner join Informix.skg skg on skg_succ = sitv_succ 
            and skg.skg_id = skr_skg.skg_id
            {$this->section($criteria)}
        left join (
            select 
                h.shre_numor, 
                h.shre_nogrp, 
                cast(h.shre_salarie as char(5)) as salarie_id,
                h.shre_date,
                sum(h.shre_qtehre) as hpointee,
                min(cast((extend(h.shre_date, year to minute) + (h.shre_debut * 60) units minute) as datetime year to second)) as hpointee_debut,
                max(cast((extend(h.shre_date, year to minute) + (h.shre_fin * 60) units minute) as datetime year to second)) as hpointee_fin
            from Informix.sav_hre h
            inner join Informix.skr r
    	        on r.skr_id = cast(h.shre_salarie as char(5))
                {$this->ressource($criteria)}
            where h.shre_soc = r.skr_soc
                {$this->numOR($criteria, 'h.shre_numor')}
                {$this->dateRange($criteria, 'h.shre_date')}
            group by h.shre_numor, h.shre_nogrp, 3, h.shre_date
        ) shre on shre.shre_numor = w.ofh_id 
            and shre.shre_nogrp = w.ofs_id * 100
            and shre.salarie_id = s.skr_id
            and shre.shre_date = date(s.ska_d_start)
        left join Informix.agr_succ agr_succ
            on	agr_succ.asuc_num = sav_itv.sitv_succ
        union all
        select distinct
            trim(skg.skg_name)										as section,
            trim(sav_itv.sitv_comment)								as intitule,
            sav_itv.sitv_numor										as numOR,
            sav_itv.sitv_interv										as itv,
            skr.skr_name											as ressource,
            round(sh.shre_qtehre / 8, 2)							as nbJour,
            cast(sh.shre_date as datetime year to second)           as date_debut,
            cast(sh.shre_date as datetime year to second)           as date_fin,
            sh.shre_qtehre                                          as hpointee,
            cast(
                (extend(sh.shre_date, year to minute) + (sh.shre_debut * 60) units minute)
                as datetime year to second)                         as hpointee_debut,
            cast(
                (extend(sh.shre_date, year to minute) + (sh.shre_fin * 60) units minute)
                as datetime year to second)                         as hpointee_fin,
            agr_succ.asuc_num ||'-'|| trim(agr_succ.asuc_lib)       as agenceEm
        from Informix.sav_hre sh
        inner join Informix.skr skr
            on skr.skr_id = trim(cast(sh.shre_salarie as char(5)))
            {$this->ressource($criteria)}
        inner join Informix.skr_skg skr_skg
            on  skr_skg.skr_id        = skr.skr_id
            and skr_skg.skr_skg_soc   = sh.shre_soc
            and skr_skg.skr_skg_succ  = sh.shre_succ
        inner join Informix.sav_itv sav_itv
            on  sav_itv.sitv_numor  = sh.shre_numor
            and sav_itv.sitv_succ   = sh.shre_succ
            and sav_itv.sitv_interv = trunc(sh.shre_nogrp / 100)
            and sav_itv.sitv_soc    = sh.shre_soc
            {$this->agenceEm($criteria, 'sav_itv.sitv_succ')}
            {$this->agenceDeb($criteria, 'sav_itv.sitv_succdeb')}
            {$this->serviceDebite($criteria, 'sav_itv.sitv_servdeb')}
            {$this->numOR($criteria, 'sav_itv.sitv_numor')}
        inner join Informix.skg skg
            on  skg.skg_id   = skr_skg.skg_id
            and skg.skg_soc  = sav_itv.sitv_soc
            and skg.skg_succ = sav_itv.sitv_succ
            {$this->section($criteria, 'skg.skg_id')}
        left join Informix.skw kw
            on  kw.ofh_id   = sh.shre_numor
            and kw.ofs_id   = sav_itv.sitv_interv
            and kw.skw_soc  = sh.shre_soc
            and kw.skw_succ = sh.shre_succ
        left join Informix.ska ka
            on  ka.skw_id         = kw.skw_id
            and ka.skr_id         = skr.skr_id
            and ka.ska_soc        = sh.shre_soc
            and date(ka.ska_d_start) = sh.shre_date
            {$this->dateRange($criteria, 'ka.ska_d_start')}
        left join Informix.agr_succ agr_succ
            on  agr_succ.asuc_num = sav_itv.sitv_succ
        where ka.skw_id IS NULL
            {$this->dateRange($criteria, 'sh.shre_date')}

        order by section,numOR
        ";
    }
}
