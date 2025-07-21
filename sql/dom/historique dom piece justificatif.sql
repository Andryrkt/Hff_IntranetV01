CREATE TABLE historique_dom_complement (
	id INT IDENTITY (1, 1),
    Numero_Ordre_Mission varchar(11) NOT NULL,
    demandeur varchar(100) not null,
    date_creation DATETIME2(0),
    date_modification DATETIME2(0),
    id_dom int not null
    CONSTRAINT PK_historique_dom_complement PRIMARY KEY (id)
);