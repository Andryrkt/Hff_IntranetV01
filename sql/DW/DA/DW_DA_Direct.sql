CREATE TABLE DW_DA_Direct (
    id int IDENTITY (1, 1) PRIMARY KEY,
	id_da_dir int NULL,
	numero_da_dir varchar(50) NULL,
    statut_da_dir VARCHAR(50) NULL,
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

CREATE NONCLUSTERED INDEX idx_dw_da_direct_id_da_dir ON DW_DA_Direct (id_da_dir);
CREATE NONCLUSTERED INDEX idx_dw_da_direct_numero_da_dir ON DW_DA_Direct (numero_da_dir);