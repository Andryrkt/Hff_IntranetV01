CREATE TABLE TKI_Planning (
    id INT IDENTITY (1, 1) NOT NULL,
    numero_ticket NVARCHAR (11) NULL, -- Homogénéité des noms de colonnes
    objet_demande VARCHAR(100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
    detail_demande VARCHAR(5000) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
    date_creation DATETIME2 (6) NOT NULL,
    date_modification DATETIME2 (6) NULL, -- Consistance entre les champs de date
    date_heure_debut_planning DATE NOT NULL,
    date_heure_fin_planning DATE NOT NULL,
    id_demande_support INT NULL,
    id_utilisateur INT NULL,
    CONSTRAINT PK_TKI_Planning PRIMARY KEY (id) -- Nom explicite pour la clé primaire
);

EXEC sp_rename 'TKI_Planning.id_utilisateur', 'user_id', 'COLUMN';

EXEC sp_rename 'TKI_Planning.id_demande_support',
'demande_id',
'COLUMN';