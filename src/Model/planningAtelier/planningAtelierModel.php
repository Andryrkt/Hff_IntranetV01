<?php

namespace App\Model\planningAtelier;

use App\Dto\PlanningAtelier\PlanningAtelierSearchDto;
use App\Model\Informix\SelectWhereCondition;
use App\Model\Model;

class planningAtelierModel extends Model
{
    private SelectWhereCondition $selectCond;

    public function __construct()
    {
        parent::__construct();
        $this->selectCond = new SelectWhereCondition();
    }

    public function getList(string $codeSociete, PlanningAtelierSearchDto $searchDto): array
    {
        $statement = $this->buildPlanningQuery($codeSociete, $searchDto);
        $statement .= " ORDER BY section, num_or, itv, ressource, date_debut, hpointee_debut ASC";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getMinMaxDates(string $codeSociete, PlanningAtelierSearchDto $searchDto): array
    {
        $baseQuery = $this->buildPlanningQuery($codeSociete, $searchDto);

        // On enveloppe la requête métier pour faire travailler le moteur SQL
        $statement = "SELECT MIN(date_debut) as min_date, MAX(date_fin) as max_date FROM ($baseQuery) as base";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        return [
            $data[0]['min_date'] ?? null,
            $data[0]['max_date'] ?? null
        ];
    }

    private function buildPlanningQuery(string $codeSociete, PlanningAtelierSearchDto $searchDto): string
    {
        // Centralisation des conditions pour éviter la désynchronisation des blocs UNION
        $conditions = "
            {$this->selectCond->eq('sitv_succdeb', $searchDto->agenceDeb)}
            {$this->selectCond->in('sitv_servdeb', $searchDto->serviceDeb)}
            {$this->selectCond->eq('sitv_succ', $searchDto->agenceEm)}
            {$this->selectCond->eq('sitv_numor', $searchDto->numeroOr)}
            {$this->selectCond->eq('skr_name', $searchDto->ressource)}
            {$this->selectCond->eq('skg.skg_id', $searchDto->section)}
        ";

        return "SELECT
            1                           as bloc,
            trim(skg_name)              as section,
            trim(sitv_comment)          as intitule,
            sitv_numor                  as num_or,
            sitv_interv                 as itv,
            trim(skr_name)              as ressource,
            round(ska_duration / 8, 2)  as nb_jour,
            s.ska_d_start               as date_debut,
            s.ska_d_end                 as date_fin,
            shre.hpointee,
            shre.hpointee_debut,
            shre.hpointee_fin,
            (select asuc_num ||'-'|| trim(asuc_lib)
            from Informix.agr_succ where asuc_num = sav_itv.sitv_succ) as agence_em
        FROM Informix.skw w
        INNER JOIN Informix.ska s on s.skw_id = w.skw_id
        INNER JOIN Informix.sav_itv sav_itv
            on sitv_numor = ofh_id
            and sitv_interv = ofs_id
        INNER JOIN Informix.skr_skg skr_skg
            on skr_skg_soc = s.ska_soc
            and skr_skg_succ = sitv_succ
            and skr_skg.skr_id = s.skr_id
        INNER JOIN Informix.skr skr on skr.skr_id = skr_skg.skr_id
        INNER JOIN Informix.skg skg on skg_succ = sitv_succ 
            and skg.skg_id = skr_skg.skg_id
        LEFT JOIN (
            select 
                h.shre_numor, 
                h.shre_nogrp, 
                cast(h.shre_salarie as char(5)) as salarie_id,
                h.shre_date,
                sum(h.shre_qtehre) as hpointee,
                min(cast((extend(h.shre_date, year to minute) + (h.shre_debut * 60) units minute) as datetime year to second)) as hpointee_debut,
                max(cast((extend(h.shre_date, year to minute) + (h.shre_fin * 60) units minute) as datetime year to second)) as hpointee_fin
            from Informix.sav_hre h
            group by 1, 2, 3, 4
        ) shre on shre.shre_numor = w.ofh_id 
            and shre.shre_nogrp = w.ofs_id * 100
            and shre.salarie_id = s.skr_id
            and shre.shre_date = date(s.ska_d_start)
        WHERE sitv_soc = ska_soc
        {$this->selectCond->between('ska_d_start', $searchDto->dateDebut, $searchDto->dateFin)}
        $conditions
        UNION ALL
        SELECT
            2                                                as bloc,
            trim(skg_name)                                   as section,
            trim(sitv_comment)                               as intitule,
            sitv_numor                                       as num_or,
            sitv_interv                                      as itv,
            trim(skr_name)                                   as ressource,
            round(sh.shre_qtehre / 8, 2)                     as nb_jour,
            cast(sh.shre_date as datetime year to second)    as date_debut,
            cast(sh.shre_date as datetime year to second)    as date_fin,
            sh.shre_qtehre                                   as hpointee,
            cast((extend(sh.shre_date, year to minute) + (sh.shre_debut * 60) units minute) as datetime year to second) as hpointee_debut,
            cast((extend(sh.shre_date, year to minute) + (sh.shre_fin * 60) units minute) as datetime year to second) as hpointee_fin,
            (select asuc_num ||'-'||  trim(asuc_lib)
            from Informix.agr_succ where asuc_num = sav_itv.sitv_succ) as agence_em
        FROM Informix.sav_hre sh
        INNER JOIN Informix.skr skr
            on skr.skr_id = cast(sh.shre_salarie as char(5))
        INNER JOIN Informix.skr_skg skr_skg
            on skr_skg.skr_id = skr.skr_id
            and skr_skg.skr_skg_soc = sh.shre_soc
            and skr_skg.skr_skg_succ = sh.shre_succ
        INNER JOIN Informix.sav_itv sav_itv
            on sav_itv.sitv_numor = sh.shre_numor
            and sav_itv.sitv_succ = sh.shre_succ
            and sav_itv.sitv_interv = trunc(sh.shre_nogrp / 100)
        INNER JOIN Informix.skg skg
            on skg.skg_id = skr_skg.skg_id
            and skg.skg_soc = sav_itv.sitv_soc
            and skg.skg_succ = sav_itv.sitv_succ
        WHERE sitv_soc = sh.shre_soc
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
        {$this->selectCond->between('shre_date', $searchDto->dateDebut, $searchDto->dateFin)}
        $conditions
        GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13";
    }

    public function getSection(string $codeSociete): array
    {
        $statement = "SELECT distinct
            trim(skg_id)        as   num,
            trim(skg_name)      as   section
        from skg, sav_itv  
        where skg_soc = '$codeSociete'
            and skg_succ = sitv_succ 
            and skg_succ = sitv_succ 
        order by num asc
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function getResource(string $codeSociete): array
    {
        $statement = "SELECT distinct
            trim(skr_name)                  as ressource
            FROM ska, skr, skr_skg, skg, skw, sav_itv
            WHERE ska_soc = '$codeSociete'
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
        return $this->convertirEnUtf8($data);
    }
}
