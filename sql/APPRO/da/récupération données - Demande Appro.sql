
INSERT INTO Demande_Appro
(numero_demande_appro, demandeur, achat_direct, numero_demande_dit, objet_dal, detail_dal, agence_emmetteur_id, Service_emmetteur_id,agence_debiteur_id, service_debiteur_id, agence_service_emmeteur,agence_service_debiteur, date_creation, date_modification, date_heure_fin_souhaitee,statut_dal, est_validee, user_id, Devis_demander, da_type_id, numero_demande_appro_mere, code_societe)
SELECT d.numero_demande_appro, d.demandeur, 0, '', d.objet_dal, d.detail_dal, a_e.id, s_e.id, a_d.id, s_d.id, d.agence_service_emmeteur, d.agence_service_debiteur, d.date_creation, d.date_modification, d.date_heure_fin_souhaitee,d.statut_dal, 0, 0, u.id, 0, 2, d.numero_demande_appro, 'HF'
FROM (
VALUES
    ('DAP26079993','mamitiana','COMMANDE MENSUELLE SERVICE','Bonjour Merci de voir la liste commande mois de juillet pour le service Mas','01','ATE','01-ATE','01','MAS','01-MAS','2026-07-01 15:13:15.000','2026-07-01 15:13:15.000','2026-07-13 00:00:00.000','Demande d’achats'),
    ('DAP26079992','mamitiana','COMMANDE MENSUELLE SERVICE HERY','MERCI DE NOUS LIVRER','01','ATE','01-ATE','01','MAS','01-MAS','2026-07-01 15:16:15.001','2026-07-01 15:16:15.001','2026-07-13 00:00:00.000','Demande d’achats')
) AS d(numero_demande_appro,demandeur,objet_dal,detail_dal,code_agence_emmeteur,code_service_emmeteur,agence_service_emmeteur,code_agence_debiteur,code_service_debiteur,agence_service_debiteur,date_creation,date_modification,date_heure_fin_souhaitee,statut_dal) 
JOIN users u 
    ON u.nom_utilisateur=d.demandeur
JOIN agences a_e
    ON a_e.code_agence=d.code_agence_emmeteur
JOIN services s_e
    ON s_e.code_service=d.code_service_emmeteur
JOIN agences a_d
    ON a_d.code_agence=d.code_agence_debiteur
JOIN services s_d
    ON s_d.code_service=d.code_service_debiteur;
