<?php

namespace App\Model\da;

use App\Model\Model;
use App\Model\Traits\ConditionModelTrait;

class DaListeCdeFrnModel extends Model
{
    use ConditionModelTrait;

    public function getInfoCdeFrn(array $criteria, string $numDitString, string $numOrString, $constRefDesisString,$listeReferenceCatalogueString): array
    {
        //les conditions de filtre
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'ref', $criteria);

        if(!empty($criteria['ref'])) {
            $ref  = REPLACE($criteria['ref'], ' ', '');
            $referencePiece = " AND REPLACE(slor_refp, '    ', '') = $ref ";
        } else {
            $referencePiece = '';
        }

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

        if (!empty($criteria['dateDebutOR'])) {
            $dateDebutORPlanning = $criteria['dateDebutOR']->format('Y-m-d');
            $dateDebutOr = " AND CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                END >= $dateDebutORPlanning";
        } else {
            $dateDebutOr = '';
        }

        if (!empty($criteria['dateFinOR'])) {
            $dateFinORPlanning = $criteria['dateFinOR']->format('Y-m-d');
            $dateFinOr = " AND CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                END <= $dateFinORPlanning";
        } else {
            $dateFinOr = '';
        }

// $statement = "SELECT distinct
// TRIM(seor_refdem) as num_dit,
// slor_numor as num_or,
// CASE 
//     WHEN 
//         (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
//     ELSE
//         (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
// END  as datePlanning,


// CASE
//     when slor_natcm = 'C' then (select fcde_numfou from frn_cde where fcde_numcde = slor_numcf)
//     when slor_natcm = 'L' then (select distinct fcde_numfou from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
// END as num_fournisseur,


// TRIM(CASE
//     when slor_natcm = 'C' then (select distinct fbse_nomfou from frn_cde inner join frn_bse on fbse_numfou = fcde_numfou where fcde_numcde = slor_numcf)
//     when slor_natcm = 'L' then (select distinct fbse_nomfou from frn_cde
//                                 inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf
//                                 inner join frn_bse on fbse_numfou = fcde_numfou)


// END) as nom_fournisseur,


// CASE


//     when slor_natcm = 'C' then (select fcde_numcde from frn_cde where fcde_numcde = slor_numcf)


//     when slor_natcm = 'L' then (select distinct fcde_numcde from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)


// END as num_cde,


// TRIM(slor_refp) as reference,
// TRIM(slor_desi) as designation,


// ROUND(
//     SUM(
//         CASE
//             when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
//         END
//     )
// ) as qte_dem,
// ROUND(
//     SUM(
//         slor_qterel
//         )
//     ) as qte_reliquat,
// ROUND(
//     SUM(
//         slor_qteres
//         )
// ) as qte_a_livrer,
// ROUND(
//     SUM(
//         slor_qterea
//         )
//     ) as qte_livee,


// CASE


// when slor_natcm = 'L' then slor_numcf


// END as num_liv,


// slor_natcm,


// slor_nolign as numero_ligne,


// slor_constp as constructeur,


// (select fcde_posc from Informix.frn_cde where fcde_numcde = slor_numcf) as position_cde





// FROM sav_lor


// INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
// INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF' and slor_nogrp / 100 = sitv_interv
// WHERE
// slor_constp = 'ZST' 
// and slor_typlig = 'P'
// and slor_refp not like ('PREST%')
// AND slor_desi not like ('%PRESTATION%')
// and TRIM(seor_refdem) IN ('DIT25069326')
// and slor_numor IN ('16418486')
// and slor_refp = 'ST'
// --AND trim(slor_constp) || '_' || TRIM(slor_refp) || '_' || slor_desi IN ('ZST_ST_POUTRELLE IPE 220MM-11,80M')


// group by 1,2,3,4,5,6,7,8,13,14,15,16,17


// UNION ALL

// SELECT distinct
// TRIM(seor_refdem) as num_dit,
// slor_numor as num_or,
// CASE 
//     WHEN 
//         (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
//     ELSE
//         (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
// END  as datePlanning,


// CASE
//     when slor_natcm = 'C' then (select fcde_numfou from frn_cde where fcde_numcde = slor_numcf)
//     when slor_natcm = 'L' then (select distinct fcde_numfou from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
// END as num_fournisseur,


// TRIM(CASE
//     when slor_natcm = 'C' then (select distinct fbse_nomfou from frn_cde inner join frn_bse on fbse_numfou = fcde_numfou where fcde_numcde = slor_numcf)
//     when slor_natcm = 'L' then (select distinct fbse_nomfou from frn_cde
//                                 inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf
//                                 inner join frn_bse on fbse_numfou = fcde_numfou)


// END) as nom_fournisseur,


// CASE


//     when slor_natcm = 'C' then (select fcde_numcde from frn_cde where fcde_numcde = slor_numcf)


//     when slor_natcm = 'L' then (select distinct fcde_numcde from frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)


// END as num_cde,


// TRIM(slor_refp) as reference,
// TRIM(slor_desi) as designation,


// ROUND(
//     SUM(
//         CASE
//             when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
//         END
//     )
// ) as qte_dem,
// ROUND(
//     SUM(
//         slor_qterel
//         )
//     ) as qte_reliquat,
// ROUND(
//     SUM(
//         slor_qteres
//         )
// ) as qte_a_livrer,
// ROUND(
//     SUM(
//         slor_qterea
//         )
//     ) as qte_livee,


// CASE


// when slor_natcm = 'L' then slor_numcf


// END as num_liv,


// slor_natcm,


// slor_nolign as numero_ligne,


// slor_constp as constructeur,


// (select fcde_posc from Informix.frn_cde where fcde_numcde = slor_numcf) as position_cde





// FROM sav_lor


// INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
// INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF' and slor_nogrp / 100 = sitv_interv
// WHERE
// slor_constp = 'ZST' 
// and slor_typlig = 'P'
// and slor_refp not like ('PREST%')
// AND slor_desi not like ('%PRESTATION%')
// and TRIM(seor_refdem) IN ('DIT25069326')
// and slor_numor IN ('16418486')
// and slor_refp <> 'ST'
// and slor_refp in ('METP013','METF023','35841')


// group by 1,2,3,4,5,6,7,8,13,14,15,16,17
// order by num_dit, num_or , num_fournisseur , nom_fournisseur , num_cde

// ";
$statement = " SELECT distinct
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


ROUND(
    SUM(
        CASE
            when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
        END
    )
) as qte_dem,
ROUND(
    SUM(
        slor_qterel
        )
    ) as qte_reliquat,
ROUND(
    SUM(
        slor_qteres
        )
) as qte_a_livrer,
ROUND(
    SUM(
        slor_qterea
        )
    ) as qte_livee,


CASE


when slor_natcm = 'L' then slor_numcf


END as num_liv,


slor_natcm,


slor_nolign as numero_ligne,


slor_constp as constructeur,


(select fcde_posc from Informix.frn_cde where fcde_numcde = slor_numcf) as position_cde





FROM Informix.sav_lor slor

INNER JOIN Informix.sav_eor seor 
    ON seor.seor_numor = slor.slor_numor 
    AND seor.seor_soc = slor.slor_soc 
    AND seor.seor_succ = slor.slor_succ 
    AND slor.slor_soc = 'HF'

INNER JOIN Informix.sav_itv sitv 
    ON sitv.sitv_numor = slor.slor_numor 
    AND sitv.sitv_soc = slor.slor_soc 
    AND sitv.sitv_succ = slor.slor_succ 
    AND sitv_interv = slor_nogrp / 100
    AND slor.slor_soc = 'HF'

-- jointure pour natcm = 'C'
LEFT JOIN Informix.frn_cde c
    ON slor.slor_natcm = 'C' 
    AND c.fcde_numcde = slor.slor_numcf
    AND c.fcde_soc = slor.slor_soc
    AND c.fcde_succ = slor.slor_succ 

-- jointure pour natcm = 'C'
LEFT JOIN Informix.frn_cdl cdl
    ON slor.slor_natcm = 'C' 
    AND cdl.fcdl_numcde = slor.slor_numcf
    AND cdl.fcdl_ligne = slor_nolign

-- jointure pour natcm = 'L'
LEFT JOIN Informix.frn_llf llf
    ON slor.slor_natcm = 'L' 
    AND llf.fllf_numliv = slor.slor_numcf 
    and llf.fllf_ligne = slor.slor_nolign

-- jointure pour natcm = 'L'
LEFT JOIN frn_cde cde 
    ON cde.fcde_numcde = llf.fllf_numcde
    AND cde.fcde_soc = llf.fllf_soc 
    AND cde.fcde_succ = llf.fllf_succ

WHERE
slor_constp = 'ZST' 
and slor_typlig = 'P'
and slor_refp not like ('PREST%')
AND slor_desi not like ('%PRESTATION%')
AND 
(
     (slor_natcm = 'C' AND c.fcde_cdeext not like 'DAL%') OR
     (slor_natcm = 'L' AND cde.fcde_cdeext not like 'DAL%')
)
and TRIM(seor_refdem) IN ($numDitString)
and slor_numor IN ($numOrString)
and slor_refp = 'ST'
AND trim(slor_constp) || '_' || TRIM(slor_refp) || '_' || slor_desi IN ($constRefDesisString)

$numDit
                $numOr
                $designation
                $referencePiece
                $numFournisseur
                $nomFournisseur
                $numCommande
                $dateDebutOr
                $dateFinOr

 group by 1,2,3,4,5,6,7,8,13,14,15,16,17



UNION


SELECT distinct
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


ROUND(
    SUM(
        CASE
            when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
        END
    )
) as qte_dem,
ROUND(
    SUM(
        slor_qterel
        )
    ) as qte_reliquat,
ROUND(
    SUM(
        slor_qteres
        )
) as qte_a_livrer,
ROUND(
    SUM(
        slor_qterea
        )
    ) as qte_livee,


CASE


when slor_natcm = 'L' then slor_numcf


END as num_liv,


slor_natcm,


slor_nolign as numero_ligne,


slor_constp as constructeur,


(select fcde_posc from Informix.frn_cde where fcde_numcde = slor_numcf) as position_cde





FROM Informix.sav_lor slor

INNER JOIN Informix.sav_eor seor 
    ON seor.seor_numor = slor.slor_numor 
    AND seor.seor_soc = slor.slor_soc 
    AND seor.seor_succ = slor.slor_succ 
    AND slor.slor_soc = 'HF'

INNER JOIN Informix.sav_itv sitv 
    ON sitv.sitv_numor = slor.slor_numor 
    AND sitv.sitv_soc = slor.slor_soc 
    AND sitv.sitv_succ = slor.slor_succ 
    AND sitv_interv = slor_nogrp / 100
    AND slor.slor_soc = 'HF'

-- jointure pour natcm = 'C'
LEFT JOIN Informix.frn_cde c
    ON slor.slor_natcm = 'C' 
    AND c.fcde_numcde = slor.slor_numcf
    AND c.fcde_soc = slor.slor_soc
    AND c.fcde_succ = slor.slor_succ 

-- jointure pour natcm = 'C'
LEFT JOIN Informix.frn_cdl cdl
    ON slor.slor_natcm = 'C' 
    AND cdl.fcdl_numcde = slor.slor_numcf
    AND cdl.fcdl_ligne = slor_nolign

-- jointure pour natcm = 'L'
LEFT JOIN Informix.frn_llf llf
    ON slor.slor_natcm = 'L' 
    AND llf.fllf_numliv = slor.slor_numcf 
    and llf.fllf_ligne = slor.slor_nolign

-- jointure pour natcm = 'L'
LEFT JOIN frn_cde cde 
    ON cde.fcde_numcde = llf.fllf_numcde
    AND cde.fcde_soc = llf.fllf_soc 
    AND cde.fcde_succ = llf.fllf_succ
WHERE
slor_constp = 'ZST' 
and slor_typlig = 'P'
and slor_refp not like ('PREST%')
AND slor_desi not like ('%PRESTATION%')
AND 
(
     (slor_natcm = 'C' AND c.fcde_cdeext not like 'DAL%') OR
     (slor_natcm = 'L' AND cde.fcde_cdeext not like 'DAL%')
)
and TRIM(seor_refdem) IN ($numDitString)
and slor_numor IN ($numOrString)
and slor_refp <> 'ST'
and slor_refp in ($listeReferenceCatalogueString)



$numDit
                $numOr
                $designation
                $referencePiece
                $numFournisseur
                $nomFournisseur
                $numCommande
                $dateDebutOr
                $dateFinOr

 group by 1,2,3,4,5,6,7,8,13,14,15,16,17


order by num_dit, num_or , num_fournisseur , nom_fournisseur , num_cde


";


        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getNumOrValideZst(string $numOrString)
    {
        $statement = " SELECT DISTINCT slor_numor as num_or
                    from Informix.sav_lor 
                    where slor_constp ='ZST' 
                    and slor_numor in ($numOrString)
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'num_or');
    }
}
