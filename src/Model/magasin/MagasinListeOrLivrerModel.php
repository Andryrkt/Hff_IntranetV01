<?php

namespace App\Model\magasin;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;
use App\Controller\Traits\FormatageTrait;
use App\Model\Traits\ConditionModelTrait;


class MagasinListeOrLivrerModel extends Model
{
    use ConversionModel;
    use FormatageTrait;
    use ConditionModelTrait;

    public function recupUserCreateNumOr($numOr)
    {
        $statement = " SELECT 
                        seor_usr as idUser, 
                        trim(ausr_nom) as nomUtilisateur,
                        trim(atab_lib) as nomPrenom
                        from sav_eor, agr_usr, agr_tab
                        where seor_usr = ausr_num
                        and ausr_ope = atab_code 
                        and atab_nom = 'OPE'
                        and seor_numor='" . $numOr . "'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning1($numOr, string $codeSociete)
    {
        $statement = " SELECT  
                            min(ska_d_start) as datePlanning1
                        from skw 
                        inner join ska on ska.skw_id = skw.skw_id 
                        where ofh_id ='$numOr'
                        AND skw_soc ='$codeSociete'
                        group by ofh_id 
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning2($numOr)
    {
        $statement = " SELECT
                            min(sitv_datepla) as datePlanning2 

                        from sav_itv 
                        where sitv_numor = '" . $numOr . "'
                        group by sitv_numor
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanningOR1($numOr, $numItv, $codeSociete)
    {
        $statement = " SELECT  
                            min(ska_d_start) as datePlanning1
                        from skw 
                        inner join ska on ska.skw_id = skw.skw_id 
                        where ofh_id ='$numOr'
                        and ofs_id = '$numItv'
                        and skw_soc ='$codeSociete'
                        group by ofh_id 
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanningOR2($numOr, $numItv, $codeSociete)
    {
        $statement = " SELECT
                            min(sitv_datepla) as datePlanning2 
                        from sav_itv 
                        where sitv_numor = '$numOr'
                        and sitv_interv = '$numItv'
                        and sitv_soc = '$codeSociete'
                        group by sitv_numor
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getDatePlanning(string $numOr)
    {
        $statement = " SELECT CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                    END as datePlanning
                    FROM informix.sav_itv 
                    WHERE  sitv_numor  = '$numOr'
                ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'datePlanning');
    }

    public function getDatePlanningPourDa(string $numOr)
    {
        $statement = "SELECT distinct(slor_numor) as num_or,
                CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM ska, skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM ska, skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                END as datePlanning
                    FROM sav_lor
                INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF'
                    WHERE  slor_numor = '$numOr'
                        and slor_typlig = 'P'
                        -- and slor_refp not like ('PREST%')
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerComplet($numOrValideItv, $criteria)
    {
        $piece = $this->conditionPiece('pieces', $criteria, 'slor_constp');

        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                            inner join sav_itv 
                                on sitv_soc = slor_soc 
                                and sitv_succ = slor_succ 
                                and sitv_numor = slor_numor 
                                and sitv_interv = slor_nogrp / 100 
                                and sitv_numor || '-' || sitv_interv in (' $numOrValideItv') 
                                and sitv_soc = 'HF'
                        WHERE slor_typlig = 'P'
                            $piece
                            GROUP BY 1
                            HAVING 
                                sum(CASE 
                                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                                END) = sum(slor_qteres) + sum(slor_qterea)
                                and sum(slor_qteres) > 0
                            order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerIncomplet($numOrValideItv, $criteria)
    {
        $piece = $this->conditionPiece('pieces', $criteria, 'slor_constp');

        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                        inner join sav_itv 
                on sitv_soc = slor_soc 
                and sitv_succ = slor_succ 
                and sitv_numor = slor_numor 
                and sitv_interv = slor_nogrp / 100 
                and sitv_numor || '-' || sitv_interv in ('$numOrValideItv') 
                and sitv_soc = 'HF'
                        WHERE  slor_typlig = 'P'
                            $piece
                        GROUP BY 1
                        HAVING 
                            sum(CASE 
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                            END) > sum(slor_qteres) + sum(slor_qterea)
                            --and sum(slor_qteres) > 0
                        order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerTout($numOrValideItv, $criteria)
    {
        $piece = $this->conditionPiece('pieces', $criteria, 'slor_constp');

        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                            inner join sav_itv 
                on sitv_soc = slor_soc 
                and sitv_succ = slor_succ 
                and sitv_numor = slor_numor 
                and sitv_interv = slor_nogrp / 100 
                and sitv_numor || '-' || sitv_interv in ('$numOrValideItv') 
                and sitv_soc = 'HF'
                        WHERE slor_typlig = 'P'
                            $piece
                        GROUP BY 1
                        HAVING 
                            sum(CASE 
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                            END) >= sum(slor_qteres) + sum(slor_qterea)
                            --and sum(slor_qteres) > 0
                        order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupereListeMaterielValider(array $criteria, string $numeroOrItv, string $numeroOr)
    {
        $statement = "SELECT 
            TRIM(seor_refdem) AS referencedit,
            seor_numor AS numeroOr,
            seor_dateor AS dateCreation,
            COALESCE(pln.date_planning_ska, DATE(sitv_datepla)) AS datePlanning,
            seor_succ AS agenceCrediteur,
            seor_servcrt AS serviceCrediteur,
            sitv_succdeb AS agenceDebiteur,
            sitv_servdeb AS serviceDebiteur,
            sitv_interv AS numInterv,
            slor_nolign AS numeroLigne,
            slor_constp AS constructeur,
            TRIM(slor_refp) AS referencePiece,
            TRIM(slor_desi) AS designationi,
            sit.situation AS situationtest,
            seor_usr AS idUser,
            TRIM(ausr_nom) AS nomUtilisateur,
            TRIM(atab_lib) AS nomPrenom,
            mmat_nummat AS idMateriel,
            TRIM(mmat_numserie) AS num_serie,
            TRIM(mmat_recalph) AS num_parc,
            TRIM(mmat_marqmat) AS marque,
            TRIM(mmat_numparc) AS casie,
            SUM(
                CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                END
            ) AS quantiteDemander,
            SUM(slor_qteres) AS qteALivrer,
            SUM(slor_qterea) AS quantiteLivree
        FROM {$this->dbIps}:Informix.sav_lor AS lor
        INNER JOIN {$this->dbIps}:Informix.sav_eor AS eor ON eor.seor_numor = slor_numor AND eor.seor_soc = slor_soc AND eor.seor_succ = slor_succ
        INNER JOIN {$this->dbIps}:Informix.mat_mat AS mat ON mat.mmat_nummat = eor.seor_nummat
        INNER JOIN {$this->dbIps}:Informix.agr_usr AS usr ON usr.ausr_num = eor.seor_usr
        INNER JOIN {$this->dbIps}:Informix.agr_tab AS tab ON tab.atab_nom = 'OPE' AND tab.atab_code = usr.ausr_ope
        INNER JOIN {$this->dbIps}:Informix.sav_itv AS itv ON itv.sitv_soc = slor_soc AND itv.sitv_succ = slor_succ AND itv.sitv_numor = slor_numor AND itv.sitv_interv = slor_nogrp / 100 AND itv.sitv_numor || '-' || itv.sitv_interv IN ($numeroOrItv)
        INNER JOIN (
            SELECT
                ls.slor_numor AS numero_or,
                SUM(ls.slor_qteres) AS total_qteres
            FROM {$this->dbIps}:Informix.sav_lor AS ls
            WHERE ls.slor_numor IN ($numeroOr)
                {$this->conditionPiece('pieces',$criteria, 'ls.slor_constp')}
            GROUP BY ls.slor_numor
        ) AS qte ON qte.numero_or = lor.slor_numor AND qte.total_qteres > 0
        INNER JOIN (
            SELECT
                ss.slor_numor AS numero_or,
                ss.slor_nogrp AS num_groupe,
                CASE
                    WHEN SUM(ss.slor_qteres) > 0
                        AND SUM(
                            CASE
                                WHEN ss.slor_typlig = 'P' THEN (ss.slor_qterel + ss.slor_qterea + ss.slor_qteres + ss.slor_qtewait - ss.slor_qrec)
                                WHEN ss.slor_typlig IN ('F','M','U','C') THEN ss.slor_qterea
                            END
                        ) = SUM(ss.slor_qteres + ss.slor_qterea)
                        THEN 'COMPLET'
                    WHEN SUM(ss.slor_qteres) > 0
                        AND SUM(
                            CASE
                                WHEN ss.slor_typlig = 'P' THEN (ss.slor_qterel + ss.slor_qterea + ss.slor_qteres + ss.slor_qtewait - ss.slor_qrec)
                                WHEN ss.slor_typlig IN ('F','M','U','C') THEN ss.slor_qterea
                            END
                        ) > SUM(ss.slor_qteres + ss.slor_qterea)
                        THEN 'INCOMPLET'
                END AS situation
            FROM {$this->dbIps}:Informix.sav_lor AS ss
            WHERE ss.slor_numor IN ($numeroOr)
                {$this->conditionPiece('pieces',$criteria, 'ss.slor_constp')}
            GROUP BY ss.slor_numor, ss.slor_nogrp
        ) AS sit ON sit.numero_or = lor.slor_numor AND sit.num_groupe = lor.slor_nogrp
        LEFT JOIN (
            SELECT
                skw.ofh_id AS num_or_planning,
                skw.ofs_id AS num_interv_planning,
                DATE(MIN(ska.ska_d_start)) AS date_planning_ska
            FROM {$this->dbIps}:Informix.ska AS ska
            INNER JOIN {$this->dbIps}:Informix.skw AS skw ON skw.skw_id = ska.skw_id
            WHERE skw.ofh_id IN ($numeroOr)
            GROUP BY skw.ofh_id, skw.ofs_id
        ) AS pln ON pln.num_or_planning = itv.sitv_numor AND pln.num_interv_planning = itv.sitv_interv
        WHERE seor_typeor NOT IN ('950', '501')
            AND sit.situation IS NOT NULL
            {$this->conditionPiece('pieces',$criteria, 'slor_constp')}
            {$this->conditionAgenceUser('agenceUser',$criteria)}
            {$this->conditionOrCompletOuNonOrALivrer('orCompletNon',$criteria, 'sit')}
            {$this->conditionLike('slor_desi', 'designation',$criteria)}
            {$this->conditionLike('slor_refp', 'referencePiece',$criteria)}
            {$this->conditionLike('slor_constp', 'constructeur',$criteria)}
            {$this->conditionDateSigne('slor_datec', 'dateDebut',$criteria, '>=')}
            {$this->conditionDateSigne('slor_datec', 'dateFin',$criteria, '<=')}
            {$this->conditionSigne('slor_numor', 'numOr', '=',$criteria)}
            {$this->conditionLike('seor_refdem', 'numDit',$criteria)}
            {$this->conditionAgenceService('slor_succdeb', 'agence',$criteria)}
            {$this->conditionAgenceService('slor_servdeb', 'service',$criteria)}
        GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22
        ORDER BY seor_numor ASC, sitv_interv ASC, slor_nolign ASC
        ";

        // dd($statement);
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getListeOrLivrerPol(array $criteria = [], string $numeroOrItv, string $numeroOr)
    {
        // dd($criteria);
        //les conditions de filtre
        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'referencePiece', $criteria);
        $constructeur = $this->conditionLike('slor_constp', 'constructeur', $criteria);
        $dateDebut = $this->conditionDateSigne('slor_datec', 'dateDebut', $criteria, '>=');
        $dateFin = $this->conditionDateSigne('slor_datec', 'dateFin', $criteria, '<=');
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $value = GlobalVariablesService::get('pneumatique');
        if (!empty($value)) {
            $piece = " AND slor_constp in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
            $piece1 = " AND situ.slor_constp in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
            $piece2 = " AND l.slor_constp in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
        } else {
            $piece = "";
            $piece1 = "";
            $piece2 = "";
        };

        $agence = $this->conditionAgenceService("slor_succdeb", 'agence', $criteria);
        $service = $this->conditionAgenceService("slor_servdeb", 'service', $criteria);
        $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);
        $orCompletNom = $this->conditionOrCompletOuNonOrALivrer('orCompletNon', $criteria);

        //requête
        $statement = " SELECT
            TRIM(seor_refdem) as referencedit
            , seor_numor as numeroOr
            , seor_dateor as dateCreation
            , CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
            END as datePlanning
            , seor_succ as agenceCrediteur
            , seor_servcrt as serviceCrediteur
            , sitv_succdeb as agenceDebiteur
            , sitv_servdeb as serviceDebiteur
            , sitv_interv as numInterv
            , slor_nolign as numeroLigne
            , slor_constp as constructeur
            , TRIM(slor_refp) as referencePiece
            , TRIM(slor_desi) as designationi
            , 
            (SELECT F.situation FROM (select
                    CASE
                        WHEN
                            sum(slor_qteres) > 0 AND
                            sum(CASE
                                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                                END) = sum(slor_qteres + slor_qterea)
                        THEN 'COMPLET'
                        WHEN sum(slor_qteres) > 0 AND
                            sum(CASE
                                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                                END) > sum(slor_qteres + slor_qterea)
                        THEN 'INCOMPLET'
                    END as situation
                    , situ.slor_numor as numero_or
                FROM sav_lor situ
                WHERE situ.slor_numor = OR.slor_numor
                    and situ.slor_constp in ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO') AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')
                group by 2 ) as F
            ) as situationtest
            , seor_usr as idUser
            , trim(ausr_nom) as nomUtilisateur
            , trim(atab_lib) as nomPrenom
            , mmat_nummat as idMateriel
            , trim(mmat_numserie) as num_serie
            , trim(mmat_recalph) as num_parc 
            , trim(mmat_marqmat) as marque
            , trim(mmat_numparc) as casie
            ,sum(CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                END)  AS quantiteDemander
            , sum(slor_qteres) as qteALivrer
            , sum(slor_qterea) as quantiteLivree




            FROM sav_lor as OR
            inner join sav_eor as U on U.seor_numor = slor_numor and U.seor_soc = slor_soc and U.seor_succ = slor_succ
            inner join mat_mat on mmat_nummat =  seor_nummat
            inner join agr_usr on ausr_num = seor_usr
            inner join agr_tab on atab_nom = 'OPE' and atab_code = ausr_ope
            inner join
                sav_itv as I
                on I.sitv_soc = slor_soc
                and I.sitv_succ = slor_succ
                and I.sitv_numor = slor_numor
                and I.sitv_interv = slor_nogrp /100
                and sitv_numor || '-' || sitv_interv in ('$numeroOrItv')
            inner join
                (SELECT F.* FROM (select
                    CASE
                        WHEN
                            sum(slor_qteres) > 0 AND
                            sum(CASE
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                                    END) = sum(slor_qteres + slor_qterea)
                        THEN 'COMPLET'
                        WHEN
                            sum(slor_qteres) > 0 AND
                            sum(CASE
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                                    END) > sum(slor_qteres + slor_qterea)
                        THEN 'INCOMPLET'
                    END as situation
                    , situ.slor_numor as numero_or
                    FROM sav_lor situ
                    WHERE situ.slor_numor in ('$numeroOr')
                        $piece1
                    group by 2 ) as F
                ) as T 
                ON T.numero_or = OR.slor_numor
            where seor_numor in
            (
            select slor_numor from sav_lor l
            where l.slor_numor  in ('$numeroOr')
            $piece2
            group by l.slor_numor
            having sum(l.slor_qteres) > 0
            )
            $piece
            and seor_typeor not in('950', '501')
            $agenceUser
                        
                        $orCompletNom
                        $designation
                        $referencePiece 
                        $constructeur 
                        $dateDebut
                        $dateFin
                        $numOr
                        $numDit
                        $agence
                        $service

            group by 1,2,3,4, 5, 6, 7, 8, 9, 10, 11, 12, 13,14,15,16,17, 18, 19, 20, 21,22
            order by seor_numor asc, sitv_interv asc, slor_nolign asc
        ";

        // dd($statement);
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperationConstructeur()
    {
        $statement = " SELECT DISTINCT
            trim(slor_constp) as constructeur
           
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc 
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and slor_typlig = 'P'
    	    and slor_constp <> '---'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_combine(array_column($this->convertirEnUtf8($data), 'constructeur'), array_column($this->convertirEnUtf8($data), 'constructeur'));
    }


    public function recupNumOr($criteria = [])
    {
        if (!empty($criteria['niveauUrgence'])) {
            $niveauUrgence = " and id_niveau_urgence = '" . $criteria['niveauUrgence']->getId() . "'";
        } else {
            $niveauUrgence = null;
        }

        if (!empty($criteria['numDit'])) {
            $numDit = " and numero_demande_dit = '" . $criteria['numDit'] . "'";
        } else {
            $numDit = null;
        }

        if (!empty($criteria['numOr'])) {
            $numOr = " and numero_or = '" . $criteria['numOr'] . "'";
        } else {
            $numOr = null;
        }

        $statement = "SELECT 
        numero_or 
        FROM demande_intervention
        WHERE (date_validation_or is not null  or date_validation_or = '1900-01-01') 
        {$niveauUrgence}
        {$numDit}
        {$numOr}
        ";


        $execQueryNumOr = $this->connexion->query($statement);

        $numOr = array();

        while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
            $numOr[] = $row_num_or;
        }

        return $numOr;
    }


    public function recupereAutocompletionDesignation($designations)
    {
        $statement = "SELECT DISTINCT
            
            trim(slor_desi) as designationi

            from sav_lor 
            
            where 
            slor_soc = 'HF'

            and slor_desi like '%" . $designations . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperAutocompletionRefPiece($refPiece)
    {
        $statement = "SELECT 
            
            trim(slor_refp) as referencePiece
        
            from sav_lor 
            
            where 
            slor_soc = 'HF'
            and slor_refp like '%" . $refPiece . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function agence()
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }

    public function service(string $agence): array
    {
        $statement = "  SELECT DISTINCT
                            slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service
                        FROM sav_lor
                        WHERE slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) <> ''
                        AND slor_soc = 'HF'
                        AND slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) = '$agence'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        $dataUtf8 = $this->convertirEnUtf8($data);

        return array_map(function ($item) {

            return [
                "value" => $item['service'],
                "text"  => $item['service']
            ];
        }, $dataUtf8);
    }


    public function agenceUser(string $codeAgence)
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM informix.sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        if ($codeAgence <> "''") {
            $statement .= " AND slor_succdeb IN ($codeAgence)";
        }

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }
}
