
SELECT  ainvp_nbordereau as numBordereau ,
 ainvp_nligne as ligne,
 (select astp_casier from art_stp WHERE astp_soc = ainvp_soc AND astp_succ = ainvp_succ AND astp_constp = ainvp_constp AND astp_refp = ainvp_refp) as casier ,
	ainvp_constp as cst, 
ainvp_refp as refp,
 (select abse_desi from art_bse WHERE abse_constp = ainvp_constp AND abse_refp = ainvp_refp) as description,
     ainvp_stktheo as qte_theo,
	(select astp_reserv from art_stp WHERE astp_soc = ainvp_soc AND astp_succ = ainvp_succ AND astp_constp = ainvp_constp AND astp_refp = ainvp_refp) as qte_alloue,
	ainvp_date as dateinv
	from art_invp
	WHERE ainvp_soc = 'HF'  
	AND ainvp_numinv = (
        select
            max(ainvi_numinv)
        from
            art_invi
        where
            ainvi_numinv_mait = '1916'
    )
	AND ainvp_nbordereau > 0
	
	order by 1,2