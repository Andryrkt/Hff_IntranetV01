
INSERT INTO Demande_Appro_L
(numero_demande_appro, num_ligne, qte_dem,  art_constp, art_refp, art_desi, art_fams1, art_fams2, numero_fournisseur, nom_fournisseur, date_fin_souhaitee_l, commentaire, statut_dal, catalogue, demande_appro_id, est_validee, est_modifier, date_creation, date_modification, numero_version, edit, prix_unitaire, numero_dit, deleted, est_fiche_technique, jours_dispo, file_names, choix, demandeur, qte_valide_appro
)
SELECT dal.numero_demande_appro, dal.num_ligne, dal.qte_dem,dar.art_constp, dar.art_refp, dar.art_desi, '-','-','-','-',da.date_heure_fin_souhaitee,'-',da.statut_dal,0,da.id,0,0,da.date_creation,da.date_modification,0,0,dar.art_pu, 0,0,0,DATEDIFF(DAY, CAST(GETDATE() AS DATE), CAST(da.date_heure_fin_souhaitee AS DATE)),'[]',1,da.demandeur,dar.qte_validee_appro 
FROM (
VALUES
    ('DAP26079993','1','3','4A0001','01','ATE'),
    ('DAP26079993','2','2','4A0008','01','ATE'),
    ('DAP26079993','3','2','4A0009','01','ATE'),
    ('DAP26079993','4','4','4A0011','01','ATE'),
    ('DAP26079993','5','8','4A0014','01','ATE'),
    ('DAP26079993','6','2','4A0026','01','ATE'),
    ('DAP26079993','7','2','1B0015','01','ATE'),
    ('DAP26079993','8','5','1C0003','01','ATE'),
    ('DAP26079993','9','5','1C0004','01','ATE'),
    ('DAP26079993','10','10','1C0012','01','ATE'),
    ('DAP26079993','11','10','1C0014','01','ATE'),
    ('DAP26079993','12','4','1E0002','01','ATE'),
    ('DAP26079993','13','5','1E0007','01','ATE'),
    ('DAP26079993','14','20','1F0002','01','ATE'),
    ('DAP26079993','15','1','1F0015','01','ATE'),
    ('DAP26079993','16','2','1F0034','01','ATE'),
    ('DAP26079993','17','2','1F0046','01','ATE'),
    ('DAP26079992','1','1','4A0007','01','ATE'),
    ('DAP26079992','2','1','4A0008','01','ATE'),
    ('DAP26079992','3','2','4A0009','01','ATE'),
    ('DAP26079992','4','2','4A0011','01','ATE'),
    ('DAP26079992','5','5','4A0014','01','ATE'),
    ('DAP26079992','6','20','1C0012','01','ATE'),
    ('DAP26079992','7','2','1F0010','01','ATE'),
    ('DAP26079992','8','1','1F0015','01','ATE'),
    ('DAP26079992','9','4','1F0041','01','ATE')
) AS dal(numero_demande_appro,num_ligne,qte_dem,art_refp,code_agence,code_service)
join Demande_Appro da 
	on dal.numero_demande_appro=da.numero_demande_appro
join da_article_reappro dar 
	on dar.code_agence=dal.code_agence and dar.code_service=dal.code_service and dar.art_refp=dal.art_refp
;
