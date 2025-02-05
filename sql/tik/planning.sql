CREATE TABLE TKI_Planning (
    id INT IDENTITY (1, 1) NOT NULL,
    numero_ticket NVARCHAR (11) NULL, -- Homogénéité des noms de colonnes
    objet_demande VARCHAR(100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
    detail_demande VARCHAR(5000) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
    date_creation DATETIME2 (6) NOT NULL,
    date_modification DATETIME2 (6) NULL, -- Consistance entre les champs de date
    date_heure_debut_planning DATETIME2 NOT NULL,
    date_heure_fin_planning DATETIME2 NOT NULL,
    demande_id INT NULL,
    user_id INT NULL,
    CONSTRAINT PK_TKI_Planning PRIMARY KEY (id) -- Nom explicite pour la clé primaire
);

CREATE TABLE TKI_Replannification (
    id INT IDENTITY (1, 1) NOT NULL,
    numero_ticket NVARCHAR (11) NULL, -- Homogénéité des noms de colonnes
    demande_id INT NULL,
    user_id INT NULL,
    old_date_heure_debut_planning DATETIME2 NOT NULL,
    old_date_heure_fin_planning DATETIME2 NOT NULL,
    new_date_heure_debut_planning DATETIME2 NOT NULL,
    new_date_heure_fin_planning DATETIME2 NOT NULL,
    date_creation DATETIME2 (6) NOT NULL,
    date_modification DATETIME2 (6) NULL, -- Consistance entre les champs de date
    CONSTRAINT PK_TKI_Replannification PRIMARY KEY (id) -- Nom explicite pour la clé primaire
);

ALTER TABLE TKI_Planning
ADD CONSTRAINT FK_Demande_Planning
FOREIGN KEY (demande_id) REFERENCES Demande_Support_Informatique (ID_Demande_Support_Informatique);

ALTER TABLE TKI_Planning
ADD CONSTRAINT FK_User_Planning
FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE TKI_Replannification
ADD CONSTRAINT FK_Demande_Replanification
FOREIGN KEY (demande_id) REFERENCES Demande_Support_Informatique (ID_Demande_Support_Informatique);

ALTER TABLE TKI_Replannification
ADD CONSTRAINT FK_User_Replanification
FOREIGN KEY (user_id) REFERENCES users (id);
