SELECT 
id, 
numero_demande_appro,    -- **** DA
numero_demande_dit,      -- **** DA
statut_dal,              -- **** DA
objet_dal,               -- **** DA
detail_dal,              -- **** DA
niveau_urgence,          -- **** DA
demandeur,               -- **** DA
achat_direct,            -- **** DA
date_demande,            -- **** DA 
agence_emmetteur_id,     -- **** DA 
Service_emmetteur_id,    -- **** DA 
agence_debiteur_id,      -- **** DA 
service_debiteur_id,     -- **** DA 
demande_appro_id,        -- **** DA 
pj_new_ate,              -- **** DaValider / DAL
num_ligne_tableau,       -- **** DaValider / DALR
pj_proposition_appro,    -- **** DaValider / DALR
num_ligne,               -- **** DaValider / DAL / DALR
qte_dem,                 -- **** DaValider / DAL / DALR
art_constp,              -- **** DaValider / DAL / DALR
art_refp,                -- **** DaValider / DAL / DALR
art_desi,                -- **** DaValider / DAL / DALR
code_fams1,              -- **** DaValider / DAL / DALR
art_fams1,               -- **** DaValider / DAL / DALR
code_fams2,              -- **** DaValider / DAL / DALR
art_fams2,               -- **** DaValider / DAL / DALR
numero_fournisseur,      -- **** DaValider / DAL / DALR
nom_fournisseur,         -- **** DaValider / DAL / DALR
date_fin_souhaitee_l,    -- **** DaValider / DAL / DALR
commentaire,             -- **** DaValider / DAL / DALR
prix_unitaire,           -- **** DaValider / DAL / DALR
total,                   -- **** DaValider / DAL / DALR
est_fiche_technique,     -- **** DaValider / DAL / DALR
nom_fiche_technique,     -- **** DaValider / DAL / DALR
valide_par,              -- **** DaValider / DAL / DALR
jours_dispo,             -- **** DaValider / DAL / DALR
est_dalr,                -- **** DaValider / DAL / DALR
numero_or,               -- TODO: dans ors_soumis_a_validation
statut_or,               -- TODO: dans ors_soumis_a_validation
pj_bc,                   -- TODO: ************** mbola
numero_cde,              -- TODO: ******************* IPS
statut_cde,              -- TODO: ******************* IPS
qte_dispo,               -- TODO: ******************* IPS
qte_en_attent,           -- TODO: ******************* IPS
qte_livrer,              -- TODO: ******************* IPS
date_livraison_prevue,   -- TODO: ************** condition
position_bc,             -- TODO: ************** condition
date_planning_or,        -- TODO: ************** condition
bc_envoyer_fournisseur,  -- TODO: ************** condition
dit_id                   -- TODO: ************** condition // ampdirin
numero_version,          -- TODO: ampdirin
or_a_resoumettre,        -- **** 
numero_ligne_ips,        -- **** 
date_creation,           -- ? automatique
date_modification,       -- ? automatique
pj_fiche_technique,      -- ! fafana
catalogue,               -- ! fafana
FROM HFF_INTRANET.dbo.da_afficher;