/*liste inventaire ligne*/
SELECT
    ainvi.ainvi_numinv_mait AS numero_inv,
    ainvi.ainvi_date AS ouvert_le,
    TRIM(ainvi.ainvi_comment) AS description,
    (SELECT COUNT(DISTINCT astp.astp_casier) FROM art_invp invp
    INNER JOIN art_stp astp ON astp.astp_succ = invp.ainvp_succ
    AND astp.astp_constp = invp.ainvp_constp
    AND astp.astp_refp = invp.ainvp_refp
    WHERE invp.ainvp_soc = ainvi.ainvi_soc
    AND invp.ainvp_succ = ainvi.ainvi_succ
    AND invp.ainvp_numinv = ainvi.ainvi_numinv
    AND invp.ainvp_stktheo <> 0
    ) AS nbre_casier,
    COUNT(s.ainvp_refp) AS nbre_ref,
    ROUND(SUM(s.ainvp_stktheo)) AS qte_comptee,
    CASE
     WHEN (SELECT  COUNT(*) FROM art_invp
            WHERE ainvp_soc = ainvi.ainvi_soc
            AND ainvp_succ = ainvi.ainvi_succ
            AND ainvp_numinv = ainvi.ainvi_numinv
            AND ainvp_ecart <> 0
        ) = 0
    AND (SELECT COUNT(*) FROM art_invp
         WHERE ainvp_soc = ainvi.ainvi_soc
         AND ainvp_succ = ainvi.ainvi_succ
         AND ainvp_numinv = ainvi.ainvi_numinv
         AND ainvp_ctrlok = 0
         AND ainvp_nbordereau > 0
        ) = 0 THEN
    'SOLDE'
    ELSE (SELECT DECODE (ainvi_cloture, 'O', 'CLOTURE', 'ENCOURS') FROM
          art_invi WHERE ainvi_numinv = ( SELECT MAX(ainvi_numinv) FROM  art_invi WHERE ainvi_numinv_mait = ainvi.ainvi_numinv_mait)        
        )
    END AS statut,
    TRUNC (SUM(s.ainvp_prix * s.ainvp_stktheo), 0) AS Montant,
    (SELECT MAX(DATE (ladm_date)) FROM log_art_invi A 
    JOIN log_adm b ON A.ladm_id = b.ladm_id
     WHERE A.ainvi_soc = ainvi.ainvi_soc
     AND A.ainvi_numinv = ( SELECT  MAX(ainvi_numinv) FROM art_invi WHERE ainvi_numinv_mait = ainvi.ainvi_numinv_mait )
     AND A.ainvi_cloture = 'O'
    ) AS date_clo
    
FROM
    art_invi ainvi
    INNER JOIN art_invp s ON s.ainvp_numinv = ainvi.ainvi_numinv_mait
WHERE
    ainvi.ainvi_soc = 'HF'
    AND ainvi.ainvi_numinv_mait = 1916
    AND ainvi.ainvi_sequence = 1
    AND (
        s.ainvp_stktheo <> 0
        OR s.ainvp_ecart <> 0
    )
    AND ainvi.ainvi_comment NOT LIKE 'KPI STOCK%'
    AND ainvi.ainvi_succ IN (
        '01',
        '02',
        '10',
        '20',
        '30',
        '40',
        '50',
        '60',
        '92'
    )
    AND ainvi.ainvi_date >= TO_DATE ('2024-03-13', '%Y-%m-%d')
GROUP BY
    ainvi.ainvi_numinv_mait,
    ainvi.ainvi_date,
    ainvi.ainvi_comment,
    ainvi.ainvi_cloture,
    nbre_casier,
    statut,
    date_clo
ORDER BY
    ainvi.ainvi_numinv_mait DESC;
/* details inventaire*/
SELECT
    ainvp_datecpt as dateInv,
    ainvp_soc as soc,
    ainvp_succ as succ,
    ainvp_constp as cst,
    TRIM(ainvp_refp) as refp,
    TRIM(abse_desi) as desi,
    TRIM(astp_casier) as casier,
    round(ainvp_stktheo) as stock_theo,
    '' as qte_comptee,
    round(ainvp_ecart) as ecart,
    CASE
        WHEN ainvp_stktheo != 0 THEN ROUND((ainvp_ecart / ainvp_stktheo) * 100) || '%'
        ELSE '0'
    END as pourcentage_nbr_ecart,
    ainvp_prix as PMP,
    ainvp_prix * ainvp_stktheo as montant_inventaire,
    ainvp_prix * ainvp_ecart as montant_ajuste,
    ROUND(
        (ainvp_prix * ainvp_ecart) / (ainvp_prix * ainvp_stktheo) * 100
    ) || '%' as pourcentage_ecart
FROM
    art_invp
    INNER JOIN art_bse on abse_constp = ainvp_constp
    and abse_refp = ainvp_refp
    INNER JOIN art_stp on astp_constp = ainvp_constp
    and astp_refp = ainvp_refp
WHERE
    ainvp_numinv = (
        select
            max(ainvi_numinv)
        from
            art_invi
        where
            ainvi_numinv_mait = '1916'
    )
    and ainvp_ecart <> 0
    and astp_casier not in ('NP', '@@@@', 'CASIER C')
group by
    1,
    2,
    3,
    4,
    5,
    6,
    7,
    8,
    9,
    10,
    11,
    12,
    13,
    14,
    15
order by
    5 asc
    /* qte compte*/
SELECT
    (ainvp_stktheo + ainvp_ecart) as qte_comptee
FROM
    art_invp
    INNER JOIN art_bse on abse_constp = ainvp_constp
    and abse_refp = ainvp_refp
    INNER JOIN art_stp on astp_constp = ainvp_constp
    and astp_refp = ainvp_refp
WHERE
    ainvp_numinv = (
        select
            ainvi_numinv
        from
            art_invi
        where
            ainvi_numinv_mait = '1916'
            and ainvi_sequence = 1
    )
    and ainvp_refp = '2441250'
    and ainvp_ecart <> 0
    and astp_casier not in ('NP', '@@@@', 'CASIER C')