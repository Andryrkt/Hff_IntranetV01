
CREATE TABLE DW_BC_Appro (
	id_bca int NULL,
	numero_bca varchar(50) NULL,
    numero_or VARCHAR(8),
	id_tiroir varchar(100) NULL,
	date_creation date NULL,
	heure_creation time(0) NULL,
	date_derniere_modification date NULL,
	heure_derniere_modification time(0) NULL,
	total_page int NULL,
	taille_fichier int NULL,
	extension_fichier varchar(10) NULL,
	[path] varchar(255) NULL,
	id int IDENTITY (1, 1) PRIMARY KEY
);


CREATE NONCLUSTERED INDEX idx_dw_facture_id_fac ON DW_Facture (id_fac);
CREATE NONCLUSTERED INDEX idx_dw_facture_numero_or ON DW_Facture (numero_or);
CREATE NONCLUSTERED INDEX idx_dw_facture_numero_fac ON DW_Facture (numero_fac);
CREATE NONCLUSTERED INDEX idx_dw_or_id_or ON DW_Ordre_De_Reparation (id_or);
CREATE NONCLUSTERED INDEX idx_dw_or_numero_or ON DW_Ordre_De_Reparation (numero_or);
CREATE NONCLUSTERED INDEX idx_dw_bca_id_bca ON DW_BC_Appro (id_bca);
CREATE NONCLUSTERED INDEX idx_dw_bca_num_da ON DW_BC_Appro (numero_da);
CREATE NONCLUSTERED INDEX idx_dw_bc_id_bc ON DW_BC_Client (id_bc);
CREATE NONCLUSTERED INDEX idx_dw_cde_id_cde ON DW_Commande (id_cde);
CREATE NONCLUSTERED INDEX idx_dw_dit_id_dit ON DW_Demande_Intervention (id_dit);
CREATE NONCLUSTERED INDEX idx_dw_dit_numero_dit ON DW_Demande_Intervention (numero_dit);
CREATE NONCLUSTERED INDEX idx_dw_devis_id_devis ON DW_Devis (id_devis);
CREATE NONCLUSTERED INDEX idx_dw_ri_id_ri ON DW_Rapport_Intervention (id_ri);

CREATE NONCLUSTERED INDEX idx_da_soumission_bc_num_da ON da_soumission_bc (numero_demande_appro);
alter table DW_BC_Appro add numero_da varchar(11);
alter table DW_BC_Appro add numero_version INT;
