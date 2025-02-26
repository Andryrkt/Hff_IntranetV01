/*liste inventaire ligne*/
SELECT
    ainvi_numinv_mait as numero_inv,
    ainvi_date as ouvert_le,
    TRIM(ainvi_comment) as description,
    (
        select
            Count(distinct astp_casier)
        from
            art_invp,
            art_stp
        WHERE
            ainvp_soc = ainvi_soc
            AND ainvp_succ = ainvi_succ
            AND ainvp_numinv = ainvi_numinv
            AND ainvp_stktheo <> 0
            AND astp_succ = ainvp_succ
            AND astp_constp = ainvp_constp
            AND astp_refp = ainvp_refp
    ) as nbre_casier,
    count(ainvp_refp) as nbre_ref,
    ROUND(sum(ainvp_stktheo)) as qte_comptee,
    CASE
        WHEN (
            select
                Count(ainvp_refp) from art_invp WHERE  ainvp_soc = ainvi_soc
                AND ainvp_succ = ainvi_succ
                AND ainvp_numinv = ainvi_numinv
                AND ainvp_ecart <> 0
                 ) = 0
             AND (
            select Count(ainvp_refp) from art_invp WHERE ainvp_soc = ainvi_soc
                AND ainvp_succ = ainvi_succ
                AND ainvp_numinv = ainvi_numinv
                AND ainvp_ctrlok = 0
                AND ainvp_nbordereau > 0
                 ) = 0 THEN 
                 'Soldé'
        ELSE 
        decode (ainvi_cloture, 'O', 'Clôturé', 'Encours')
    END as statut,
    trunc (sum(ainvp_prix * ainvp_stktheo)) as Montant
FROM
    art_invi
    INNER JOIN art_invp ON ainvp_numinv = ainvi_numinv_mait
WHERE
    ainvi_soc = 'HF'
    AND ainvi_sequence = 1
    AND (
        ainvp_stktheo <> 0
        or (ainvp_ecart <> 0)
    )
    AND ainvi_succ IN ('01')
    AND ainvi_date >= TO_DATE ('2025-02-01', '%Y-%m-%d')
    AND ainvi_date <= TO_DATE ('2025-02-26', '%Y-%m-%d')
group by
    ainvi_numinv_mait,
    ainvi_date,
    ainvi_comment,
    ainvi_cloture,
    nbre_casier,
    statut
order by
    ainvi_numinv_mait desc


    
    /* details inventaire*/
SELECT
    ainvp_soc,
    ainvp_succ,
    ainvp_constp,
    ainvp_refp,
    abse_desi,
    astp_casier,
    ainvp_stktheo,
    '' as qte_comptee,
    ainvp_ecart,
    CASE
        WHEN ainvp_stktheo != 0 THEN ROUND((ainvp_ecart / ainvp_stktheo) * 100) || '%'
        ELSE '0'
    END as pourcentage_nbr_ecart,
    ainvp_prix as PMP,
    ainvp_prix * ainvp_stktheo as montant_inventaire,
    ainvp_prix * ainvp_ecart as montant_ajuste
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