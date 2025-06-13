<?php

namespace App\Model\da;

use App\Model\Model;
use App\Model\Traits\ConditionModelTrait;

class DaListeCdeFrnModel extends Model
{
    use ConditionModelTrait;

    public function getInfoCdeFrn(string $numDitString, array $criteria): array
    {
        //les conditions de filtre
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'ref', $criteria);

        if (!empty($criteria['numFrn'])) {
            $numFrn = $criteria['numFrn'];
            $numFournisseur = "AND CASE
                    when slor_natcm = 'C' then (select fcde_numfou from frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_numfou from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END = $numFrn";
        } else {
            $numFournisseur = '';
        }

        if (!empty($criteria['frn'])) {
            $nomFrn = $criteria['frn'];
            $nomFournisseur = "AND CASE
                    when slor_natcm = 'C' then (select distinct fbse_nomfou from frn_cde inner join frn_bse on fbse_numfou = fcde_numfou where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fbse_nomfou from frn_cde
                                                inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf
                                                inner join frn_bse on fbse_numfou = fcde_numfou)
                END = $nomFrn";
        } else {
            $nomFournisseur = '';
        }

        if (!empty($criteria['numCde'])) {
            $numCde = $criteria['numCde'];
            $numCommande = "AND CASE
                    when slor_natcm = 'C' then (select fcde_numcde from frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_numcde from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END = $numCde";
        } else {
            $numCommande = '';
        }

        //requête
        $statement = "SELECT
                TRIM(seor_refdem) as num_dit,
                slor_numor as num_or,
                CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                END  as datePlanning,
                CASE
                    when slor_natcm = 'C' then (select fcde_numfou from frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_numfou from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END as num_fournisseur,
                TRIM(CASE
                    when slor_natcm = 'C' then (select distinct fbse_nomfou from frn_cde inner join frn_bse on fbse_numfou = fcde_numfou where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fbse_nomfou from frn_cde
                                                inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf
                                                inner join frn_bse on fbse_numfou = fcde_numfou)
                END) as nom_fournisseur,
                CASE
                    when slor_natcm = 'C' then (select fcde_numcde from frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_numcde from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END as num_cde,
                TRIM(slor_refp) as reference,
                TRIM(slor_desi) as designation,
                ROUND(CASE
                    when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                END) as qte_dem,
                ROUND(slor_qterel) as qte_reliquat,
                ROUND(slor_qteres) as qte_a_livrer,
                ROUND(slor_qterea) as qte_livee,
                CASE
                when slor_natcm = 'L' then slor_numcf
                END as num_liv,
                slor_natcm,
                slor_nolign as numero_ligne,
                slor_constp as constructeur
                FROM sav_lor
                INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
                INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF'
                WHERE
                slor_constp = 'ZST' 
                and slor_refp <> 'ST'
                and slor_typlig = 'P'
                and slor_natcm in ('C', 'L')
                and slor_refp not like ('PREST%')
                --and TRIM(seor_refdem) IN ($numDitString)
                $numDit
                $numOr
                $designation
                $referencePiece
                $numFournisseur
                $nomFournisseur
                $numCommande
                order by num_dit, num_or , num_fournisseur , nom_fournisseur , num_cde
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
