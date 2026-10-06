CREATE TABLE DW_BC_Client_Negoce (
    id int IDENTITY (1, 1) PRIMARY KEY,
	id_bcc_neg int NULL,
	numero_bcc_neg varchar(100) NULL,
    numero_devis VARCHAR(50) NULL,
    statut_bcc_neg VARCHAR(100) NULL,
    numero_version INT NULL,
	id_tiroir varchar(255) NULL,
	date_creation date NULL,
	heure_creation time(0) NULL,
	date_derniere_modification date NULL,
	heure_derniere_modification time(0) NULL,
	total_page int NULL,
	taille_fichier int NULL,
	extension_fichier varchar(10) NULL,
	[path] varchar(255) NULL
);

CREATE NONCLUSTERED INDEX idx_dw_bc_client_negoce_id_bcc_neg ON DW_BC_Client_Negoce (id_bcc_neg);
CREATE NONCLUSTERED INDEX idx_dw_bc_client_negoce_numero_bcc_neg ON DW_BC_Client_Negoce (numero_bcc_neg);
CREATE NONCLUSTERED INDEX idx_dw_bc_client_negoce_numero_devis ON DW_BC_Client_Negoce (numero_devis);