EXEC sp_rename 'TKI_SOUS_CATEGORIE.Date_Creation',
'date_creation',
'COLUMN';

ALTER TABLE TKI_SOUS_CATEGORIE ADD date_modification DATE

CREATE TABLE souscategorie_autrescategories (
    souscategorie_id INT,
    autrescategorie_id INT,
    CONSTRAINT PK_souscategorie_autrescategories PRIMARY KEY (
        souscategorie_id,
        autrescategorie_id
    ),
    CONSTRAINT FK_souscategorie_autrescategories_souscategorie_id FOREIGN KEY (souscategorie_id) REFERENCES TKI_SOUS_CATEGORIE (ID_Sous_Categorie),
    CONSTRAINT FK_souscategorie_autrescategories_autrescategorie_id FOREIGN KEY (autrescategorie_id) REFERENCES TKI_Autres_Categorie (ID_Autres_Categorie)
);

ALTER TABLE TKI_SOUS_CATEGORIE ALTER COLUMN date_creation DATETIME2