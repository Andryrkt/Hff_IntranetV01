
/*liste inventaire ligne*/
select 
count(distinct ainvp_refp) as nombre_ref,

trunc(SUM(ainvp_stktheo * ainvp_prix)) as Mont_Total,

SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) AS nbre_ref_ecarts_positif,

SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END) AS nbre_ref_ecarts_negatifs,

'total_nombre_ref_ecart_pos_plus_neg' as nbre_ref_en_ecart,
SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) +
SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END) AS total_nbre_ref_ecarts
,

'nbre_ref_en_ecart_diviser_par_nbre_ref_fois_100' as pourcentage_ref_avec_ecart,
CONCAT(
    ROUND(
        (SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) +
        SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END)) 
        / COUNT(DISTINCT ainvp_refp) * 100), 
    '%'
) AS pourcentage_ref_avec_ecart,

trunc(SUM(ainvp_ecart * ainvp_prix)) as montant_ecart,

'montant_ecart_diviser_par_montant_total_fois_100' as pourcentage_ecart,
CONCAT(
    TRUNC(
        (SUM(ainvp_ecart * ainvp_prix) / SUM(ainvp_stktheo * ainvp_prix)) * 100), 
    '%'
) AS pourcentage_ecart
from art_invp where 
 (ainvp_stktheo <> 0 or ( ainvp_ecart <> 0 ))
and ainvp_numinv = '1919'


/* details inventaire*/
SELECT ainvp_soc, ainvp_succ, ainvp_constp, ainvp_refp, abse_desi, astp_casier, ainvp_stktheo, 
'' as qte_comptee, ainvp_ecart,
ROUND((ainvp_ecart / ainvp_stktheo) * 100 )|| '%' as porcentage_nbr_ecart,
ainvp_prix as PMP,
ainvp_prix * ainvp_stktheo as montant_inventaire,
ainvp_prix * ainvp_ecart as montant_ajuste
FROM art_invp
INNER JOIN art_bse on abse_constp = ainvp_constp and abse_refp = ainvp_refp
INNER JOIN art_stp on astp_constp = ainvp_constp and astp_refp = ainvp_refp
WHERE ainvp_numinv = (select max(ainvi_numinv) from art_invi where ainvi_numinv_mait = '1916')
and ainvp_ecart <> 0 and astp_casier not in ('NP','@@@@','CASIER C')


