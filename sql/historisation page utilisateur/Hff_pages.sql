CREATE TABLE Hff_pages (
	id INT IDENTITY (1, 1) ,
    nom VARCHAR(255) NOT NULL,
    nom_route varchar(255) NOT NULL,
    lien VARCHAR(255) NOT NULL
    CONSTRAINT PK_hff_pages PRIMARY KEY (id)
);
