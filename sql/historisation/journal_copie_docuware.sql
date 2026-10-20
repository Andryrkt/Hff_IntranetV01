-- ============================================================
-- Table : journal_copie_docuware (SQL Server)
-- Une ligne par tentative de copie d'un fichier vers DocuWare
-- (alimentée par GeneratePdf::copyFile / copierEtJournaliser)
-- ============================================================
CREATE TABLE
    journal_copie_docuware (
        id INT IDENTITY (1, 1) NOT NULL,
        date_copie DATETIME2 NOT NULL DEFAULT GETDATE (),
        utilisateur VARCHAR(100) NULL,
        url VARCHAR(500) NULL,
        fichier_source VARCHAR(500) NOT NULL,
        source_existe BIT NOT NULL DEFAULT 0,
        taille_source BIGINT NULL,
        destination VARCHAR(500) NOT NULL,
        dossier_docuware VARCHAR(150) NULL,
        nom_fichier VARCHAR(255) NULL,
        succes BIT NOT NULL DEFAULT 0,
        nb_tentatives INT NOT NULL DEFAULT 1,
        message_erreur VARCHAR(1000) NULL,
        est_vu BIT NOT NULL DEFAULT 0,
        CONSTRAINT PK_journal_copie_docuware PRIMARY KEY (id)
    );

CREATE INDEX IDX_journal_copie_dw_date ON journal_copie_docuware (date_copie);

CREATE INDEX IDX_journal_copie_dw_nom_fichier ON journal_copie_docuware (nom_fichier);

CREATE INDEX IDX_journal_copie_dw_echec_non_vu ON journal_copie_docuware (succes, est_vu);
