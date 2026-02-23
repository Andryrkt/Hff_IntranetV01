CREATE TABLE devis_soumis_a_validation_neg
(
    id int IDENTITY(1,1) NOT NULL,
    numero_devis varchar(11) NOT NULL,
    numero_version INT NOT NULL DEFAULT 0,
    statut_dw varchar(100),
    nombre_lignes INT NOT NULL DEFAULT 0,
    montant_devis DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    devise varchar(3) NOT NULL,
    type_soumission varchar(2) NOT NULL,
    date_maj_statut DATETIME2(0) null,
    utilisateur varchar(100) not null,
    cat BIT NOT NULL DEFAULT 0,
    non_cat BIT NOT NULL DEFAULT 0,
    nom_fichier varchar(255) null,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_devis_soumis_a_validation_neg PRIMARY KEY (id)
);

ALTER TABLE devis_soumis_a_validation_neg
    ADD date_envoye_devis_client DATETIME2(0) NULL,
    somme_numero_lignes INT NOT NULL DEFAULT 0,
    date_pointage DATETIME2(0) NULL,
    tache_validateur TEXT NULL,
    statut_bc VARCHAR(100) NULL,
    relance VARCHAR(50) NULL,
    est_validation_pm BIT DEFAULT 0,
    date_bc DATETIME2(0) NULL,
    observation VARCHAR(5000) NULL,
    piece_joint_excel varchar(255) null,
    migration bit default 0,
    statut_temp VARCHAR(255);

ALTER TABLE devis_soumis_a_validation_neg
    ADD statut_relance VARCHAR(50) NULL;


CREATE TABLE pointage_relance
(
    id int IDENTITY(1,1) NOT NULL,
    numero_devis varchar(11) NOT NULL,
    numero_version INT NULL DEFAULT 0,
    date_de_relance DATETIME2(0) not null,
    utilisateur varchar(100) not null,
    societe VARCHAR(5) NULL,
    agence VARCHAR(2) NULL,
    date_creation DATETIME2(0) not null,
    date_modification DATETIME2(0) not null,
    CONSTRAINT PK_pointage_relance PRIMARY KEY (id)
);


ALTER TABLE pointage_relance
    ADD numero_relance INT NULL;


ALTER TABLE devis_soumis_a_validation_neg 
ADD stop_progression_global BIT DEFAULT 0,
    date_stop_global DATETIME NULL,
    motif_stop_global VARCHAR(255) NULL,
    -- Permet de reprendre manuellement si besoin
    date_reprise_manuel DATETIME NULL;

-- 2. Dans la table des relances (pour un stop par niveau)
ALTER TABLE pointage_relance 
ADD stop_progression_niveau BIT DEFAULT 0,
    date_stop_niveau DATETIME NULL,
    motif_stop_niveau VARCHAR(255) NULL;

-- Ajouter une table d'historique des changements d'état
CREATE TABLE historique_stop_reprise
(
    id INT IDENTITY(1,1) PRIMARY KEY,
    numero_devis VARCHAR(50),
    type_action VARCHAR(20),
    -- 'STOP_GLOBAL', 'REPRISE_GLOBAL', 'STOP_NIVEAU', 'REPRISE_NIVEAU'
    niveau_relance INT NULL,
    -- 1,2,3 ou NULL pour global
    date_action DATETIME DEFAULT GETDATE(),
    motif VARCHAR(255),
    utilisateur VARCHAR(100)
);

-- Trigger pour tracer automatiquement les changements
CREATE TRIGGER trg_trace_stop_reprise_devis
ON devis_soumis_a_validation_neg
AFTER UPDATE
AS
BEGIN
    IF UPDATE(stop_progression_global)
    BEGIN
        INSERT INTO historique_stop_reprise
            (numero_devis, type_action, date_action, motif)
        SELECT
            i.numero_devis,
            CASE WHEN i.stop_progression_global = 1 THEN 'STOP_GLOBAL' ELSE 'REPRISE_GLOBAL' END,
            GETDATE(),
            i.motif_stop_global
        FROM inserted i
            JOIN deleted d ON i.numero_devis = d.numero_devis
        WHERE ISNULL(i.stop_progression_global, 0) != ISNULL(d.stop_progression_global, 0);
    END
END;