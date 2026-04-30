CREATE TABLE DW_Contrat (
    id int IDENTITY (1, 1) PRIMARY KEY,
	id_contrat int NULL,
	ref_contrat varchar(255) NULL,
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

CREATE NONCLUSTERED INDEX idx_dw_contrat_id_contrat ON DW_Contrat (id_contrat);
CREATE NONCLUSTERED INDEX idx_dw_contrat_ref_contrat ON DW_Contrat (ref_contrat);