CREATE TABLE DW_DA_Reappro (
    id int IDENTITY (1, 1) PRIMARY KEY,
	id_da_reap int NULL,
	numero_da_reap varchar(50) NULL,
    statut_da_reap VARCHAR(50) NULL,
	id_tiroir varchar(255) NULL,
    numero_version INT NULL,
	date_creation date NULL,
	heure_creation time(0) NULL,
	date_derniere_modification date NULL,
	heure_derniere_modification time(0) NULL,
	total_page int NULL,
	taille_fichier int NULL,
	extension_fichier varchar(10) NULL,
	[path] varchar(255) NULL
);

CREATE NONCLUSTERED INDEX idx_dw_da_reappro_id_da_reap ON DW_DA_Reappro (id_da_reap);
CREATE NONCLUSTERED INDEX idx_dw_da_reappro_numero_da_reap ON DW_DA_Reappro (numero_da_reap);