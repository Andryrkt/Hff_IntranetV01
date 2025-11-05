CREATE TABLE DW_Bon_de_caisse (
    id int IDENTITY (1, 1) PRIMARY KEY,
	id_bcs int NULL,
	numero_bcs varchar(50) NULL,
    statut_bcs VARCHAR(50) NULL,
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

CREATE NONCLUSTERED INDEX idx_dw_bon_de_caisse_id_bcs ON DW_Bon_de_caisse (id_bcs);
CREATE NONCLUSTERED INDEX idx_dw_bon_de_caisse_numero_bcs ON DW_Bon_de_caisse (numero_bcs);