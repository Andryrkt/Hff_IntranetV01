CREATE TABLE historique_demande_modif_DA (
	id INT IDENTITY (1, 1) ,
    numero_demande_appro varchar(11) NOT NULL,
    demandeur varchar(100) not null,
    motif VARCHAR(255) NOT NULL,
    demande_appro_id int not null,
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),
    est_deverouillee bit DEFAULT 0,
    CONSTRAINT PK_historique_demande_modif_DA PRIMARY KEY (id)
);