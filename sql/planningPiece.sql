SELECT slor_numor as numOr,
                            sitv_interv as Intv,
                            trim(slor_constp) as cst,
                            trim(slor_refp) as ref,
                            trim(slor_desi) as desi,
                            slor_qterel AS QteReliquat,
                            (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AS QteRes_Or,
                            slor_qterea AS Qteliv,
                            slor_qteres AS QteAll,
                            
                     CASE  WHEN slor_natcm = 'C' THEN 
                     'COMMANDE'
                      WHEN slor_natcm = 'L' THEN 
                      'RECEPTIONNE'
                      END AS Statut_ctrmq,
                      CASE WHEN slor_natcm = 'C' THEN 
                      slor_numcf
                      WHEN slor_natcm = 'L' THEN 
                      (SELECT fllf_numcde FROM frn_llf WHERE fllf_numliv = slor_numcf
                      AND fllf_ligne = slor_noligncm
                      AND fllf_refp = slor_refp)
                      END AS numeroCmd,

                      CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0  THEN
                      'A LIVRER'
                      WHEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) = slor_qteres AND slor_qterel  = 0 AND  slor_qterea = 0THEN
                      'DISPO STOCK'
                      WHEN slor_qterea = 
                      (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                      'LIVRER'
                      ELSE 
                      ( SELECT libelle_type
                        FROM gcot_acknow_cat
                        WHERE Numero_PO = (SELECT fllf_numcde 
                                           FROM frn_llf 
                                           WHERE fllf_numliv = slor_numcf 
                                           AND fllf_ligne = slor_noligncm
                                           AND fllf_refp = slor_refp)
                        AND Parts_Number = slor_refp
                        AND Parts_CST = slor_constp
                        AND Line_Number = slor_noligncm
                        AND  id_gcot_acknow_cat = (SELECT MAX(id_gcot_acknow_cat)
                                                   FROM gcot_acknow_cat
                                                   WHERE Numero_PO = (SELECT fllf_numcde 
						                                                          FROM frn_llf 
                                                                      WHERE fllf_numliv = slor_numcf
                                                                      AND fllf_ligne = slor_noligncm
                                                                      AND fllf_refp = slor_refp )    
					                                          AND Parts_Number = slor_refp
                                                    AND Parts_CST = slor_constp
                                                    AND Line_Number = slor_noligncm)
		                    )
	                    END as Statut,

                    CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0  THEN
                    TO_CHAR((
		                        (SELECT spic_datepic 
                            FROM sav_pic
                            WHERE spic_numor = slor_numor
                            AND spic_refp = slor_refp
                            AND spic_nolign = slor_nolign )), '%Y-%m-%d')
	                  WHEN slor_qterea = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                  	TO_CHAR((
		                        (SELECT sliv_date 
		                        FROM sav_liv 
                            WHERE sliv_numor = slor_numor 
		                        AND sliv_nolign = slor_nolign)), '%Y-%m-%d')
	                  ELSE	
	                  TO_CHAR((
		                      ( SELECT date_creation 
                            FROM gcot_acknow_cat
                            WHERE Numero_PO = (SELECT fllf_numcde
                                                FROM frn_llf
                                                WHERE fllf_numliv = slor_numcf
                                                AND fllf_ligne = slor_noligncm
                                                AND fllf_refp = slor_refp)
                            AND Parts_Number = slor_refp
                            AND Parts_CST = slor_constp
                            AND Line_Number = slor_noligncm
                            AND  id_gcot_acknow_cat = (SELECT MAX(id_gcot_acknow_cat)
                                                        FROM gcot_acknow_cat
                                                        WHERE Numero_PO = (SELECT fllf_numcde
                                                        FROM frn_llf 
                                                        WHERE fllf_numliv = slor_numcf
                                                        AND fllf_ligne = slor_noligncm
                                                        AND fllf_refp = slor_refp)    
				                  	AND Parts_Number = slor_refp
					                  AND Parts_CST = slor_constp     
				                  	AND Line_Number = slor_noligncm)
		                       )
                            ), '%Y-%m-%d')
	                    END AS dateStatut             

                FROM sav_lor
	              JOIN sav_itv ON slor_numor = sitv_numor 
                AND sitv_interv = slor_nogrp / 100
                WHERE slor_numor || '-' || sitv_interv = '".$numOrIntv."'
                AND slor_typlig = 'P'
                AND slor_constp NOT LIKE '%ZDI%'