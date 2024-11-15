EXEC sp_rename 'TKI_CATEGORIE.Date_Creation',
'date_creation',
'COLUMN';

INSERT INTO
    TKI_CATEGORIE (
        Description,
        date_creation,
        date_modification
    )
VALUES (
        'APPLICATION METIER',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'MATERIELS',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'BUREAUTIQUE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SECURITE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'MESSAGERIE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'RESEAU INFORMATIQUE',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SERVICE DIVERS',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'SERVICE INTERNET',
        '2024-11-07',
        '2024-11-07'
    ),
    (
        'REPORTING',
        '2024-11-07',
        '2024-11-07'
    )

CREATE TABLE categorie_souscategorie (
    tkicategorie_id INT,
    tkisouscategorie_id INT,
    CONSTRAINT PK_categorie_souscategorie PRIMARY KEY (
        tkicategorie_id,
        tkisouscategorie_id
    ),
    CONSTRAINT FK_categorie_souscategorie_tkicategorie_id FOREIGN KEY (tkicategorie_id) REFERENCES TKI_CATEGORIE (id),
    CONSTRAINT FK_categorie_souscategorie_tkisouscategorie_id FOREIGN KEY (tkisouscategorie_id) REFERENCES TKI_SOUS_CATEGORIE (id)
);

CREATE TABLE TKI_CATEGORIE (
    id INT IDENTITY (1, 1),
    description VARCHAR(100),
    date_creation DATETIME2 (3),
    date_modification DATETIME2 (3) CONSTRAINT PK_tki_categorie PRIMARY KEY (id),
);