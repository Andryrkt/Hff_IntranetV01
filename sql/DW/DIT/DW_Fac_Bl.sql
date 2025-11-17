
CREATE TABLE DW_FAC_BL (
	id_fac_bl int NULL,
	numero_fac_bl varchar(50) NULL,
	numero_da varchar(11),
    numero_or VARCHAR(8),
	id_tiroir varchar(100) NULL,
	numero_version INT NULL,
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

CREATE NONCLUSTERED INDEX idx_dw_fac_bl_id_fac_bl ON DW_FAC_BL (id_fac_bl);
CREATE NONCLUSTERED INDEX idx_dw_fac_bl_num_da ON DW_FAC_BL (numero_da);

alter table DW_FAC_BL add nom_fichier_scannee varchar(255) NULL;
alter table DW_FAC_BL add nom_fichier_dw varchar(255) NULL;
alter table DW_FAC_BL add numero_bc varchar(50) NULL;