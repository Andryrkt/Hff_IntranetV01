EXEC sp_rename 'TKI_SOUS_CATEGORIE.Date_Creation',
'date_creation',
'COLUMN';

ALTER TABLE TKI_SOUS_CATEGORIE ADD date_modification DATE

CREATE TABLE souscategorie_autrescategories (
    tkisouscategorie_id INT,
    tkiautrescategorie_id INT,
    CONSTRAINT PK_souscategorie_autrescategories PRIMARY KEY (
        tkisouscategorie_id,
        tkiautrescategorie_id
    ),
    CONSTRAINT FK_souscategorie_autrescategories_tkisouscategorie_id FOREIGN KEY (tkisouscategorie_id) REFERENCES TKI_SOUS_CATEGORIE (id),
    CONSTRAINT FK_souscategorie_autrescategories_tkiautrescategorie_id FOREIGN KEY (tkiautrescategorie_id) REFERENCES TKI_Autres_Categorie (id)
);

ALTER TABLE TKI_SOUS_CATEGORIE ALTER COLUMN date_creation DATETIME2

CREATE TABLE TKI_SOUS_CATEGORIE (
    id INT IDENTITY (1, 1),
    description VARCHAR(100),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3) CONSTRAINT PK_tki_sous_categorie PRIMARY KEY (id),
);