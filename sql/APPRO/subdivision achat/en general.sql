alter table Demande_Appro alter column numero_demande_appro varchar(12) null;

alter table Demande_Appro_L alter column numero_demande_appro varchar(12) null;

alter table Demande_Appro_L_R alter column numero_demande_appro varchar(12) null;

alter table da_afficher alter column numero_demande_appro varchar(12) null;

alter table da_observation alter column numero_da varchar(12) null;

alter table da_soumis_a_validation alter column numero_demande_appro varchar(12) null;

alter table da_soumission_bc alter column numero_demande_appro varchar(12) null;

alter table da_soumission_facture_bl alter column numero_demande_appro varchar(12) null;

alter table DW_BC_Appro alter column numero_da varchar(12) null;

alter table Demande_Appro add numero_demande_appro_mere varchar(12) null;