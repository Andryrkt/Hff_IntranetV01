EXEC sp_rename 'TKI_Autres_Categorie.Date_Creation',
'date_creation',
'COLUMN';


CREATE TABLE TKI_Autres_Categorie (
    id INT IDENTITY (1, 1),
    description VARCHAR(100),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3) CONSTRAINT PK_tki_autres_categorie PRIMARY KEY (id),
);