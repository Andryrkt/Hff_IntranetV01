-- ============================================================
-- Schéma : Demande de diagnostic pneu (SQL Server)
-- ============================================================
-- ------------------------------------------------------------
-- Table : chantier
-- ------------------------------------------------------------
CREATE TABLE
    chantier (
        id_chantier INT IDENTITY (1, 1) NOT NULL,
        code_chantier VARCHAR(20) NOT NULL,
        nom_chantier VARCHAR(150) NOT NULL,
        actif BIT NOT NULL DEFAULT 1,
        date_creation DATETIME2 NOT NULL DEFAULT GETDATE (),
        CONSTRAINT PK_chantier PRIMARY KEY (id_chantier),
        CONSTRAINT UQ_chantier_code UNIQUE (code_chantier)
    );

CREATE INDEX IDX_chantier_code ON chantier (code_chantier);

CREATE INDEX IDX_chantier_nom ON chantier (nom_chantier);

CREATE INDEX IDX_chantier_actif ON chantier (actif);

INSERT INTO
    chantier (code_chantier, nom_chantier, actif, date_creation)
VALUES
    (N'LSC', N'SAMCRETE', 1, GETDATE ()),
    (N'LGS', N'Logisitique STAR', 1, GETDATE ());

-- ------------------------------------------------------------
-- Table : demande_diagnostic_pneu
-- ------------------------------------------------------------
CREATE TABLE
    demande_diagnostic_pneu (
        id BIGINT IDENTITY (1, 1) PRIMARY KEY,
        numero_demande VARCHAR(12) NOT NULL UNIQUE,
        id_chantier INT NOT NULL,
        id_materiel INT NOT NULL,
        numero_parc_materiel VARCHAR(20) NULL,
        marque_materiel VARCHAR(50) NULL,
        type_materiel VARCHAR(50) NULL,
        designation_materiel VARCHAR(150) NULL,
        date_depart_chantier DATE NOT NULL,
        livraison VARCHAR(10) CHECK (livraison IN ('Machine', 'Pneu')),
        nb_pneu_sur_machine SMALLINT NOT NULL CHECK (nb_pneu_sur_machine >= 0),
        nb_pneu_secours SMALLINT NOT NULL CHECK (nb_pneu_secours >= 0),
        nb_pneu_a_diagnostiquer SMALLINT NOT NULL CHECK (nb_pneu_a_diagnostiquer BETWEEN 0 AND 10),
        observation VARCHAR(MAX),
        observation_global_atelier VARCHAR(MAX),
        piecesJointesAtelier NVARCHAR (MAX) NULL DEFAULT '[]',
        demandeur VARCHAR(100) NOT NULL,
        mailDemandeur VARCHAR(100) NOT NULL,
        date_creation DATETIME2 NOT NULL DEFAULT SYSDATETIME (),
        statut VARCHAR(20) NOT NULL DEFAULT 'a traiter atelier',
        numero_dit VARCHAR(12),
        numero_or VARCHAR(20),
        motifs NVARCHAR (MAX) NOT NULL DEFAULT '[]',
        piecesJointes NVARCHAR (MAX) NULL DEFAULT '[]',
        CONSTRAINT FK_demande_diagnostic_pneu_chantier FOREIGN KEY (id_chantier) REFERENCES chantier (id_chantier)
    );

CREATE INDEX IDX_ddp_chantier ON demande_diagnostic_pneu (id_chantier);

CREATE INDEX IDX_ddp_statut ON demande_diagnostic_pneu (statut);

CREATE INDEX IDX_ddp_dit ON demande_diagnostic_pneu (numero_dit);

CREATE INDEX IDX_ddp_materiel ON demande_diagnostic_pneu (id_materiel);

-- ------------------------------------------------------------
-- Table : diagnostic_pneu (saisie atelier, jusqu'à 10 lignes par demande)
-- ------------------------------------------------------------
CREATE TABLE
    diagnostic_pneu (
        id BIGINT IDENTITY (1, 1) PRIMARY KEY,
        id_demande BIGINT NOT NULL,
        numero_demande VARCHAR(12) NULL,
        numero_ligne SMALLINT NOT NULL CHECK (numero_ligne BETWEEN 1 AND 10),
        ns_pneu VARCHAR(50) NOT NULL,
        cote_dim VARCHAR(30) NOT NULL,
        position_machine VARCHAR(50) NOT NULL,
        motif_chantier VARCHAR(100) NOT NULL,
        diagnostic VARCHAR(20) CHECK (
            diagnostic IN ('reparable', 'remplacer', 'rechapable', 'detruit')
        ),
        observation_atelier VARCHAR(MAX),
        date_diagnostic DATETIME2 NULL,
        CONSTRAINT FK_diagnostic_pneu_demande FOREIGN KEY (id_demande) REFERENCES demande_diagnostic_pneu (id) ON DELETE CASCADE,
        CONSTRAINT UQ_demande_ligne_diag UNIQUE (id_demande, numero_ligne)
    );

CREATE INDEX IDX_diag_pneu_demande ON diagnostic_pneu (id_demande);