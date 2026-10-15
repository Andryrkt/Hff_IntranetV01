-- ============================================================
-- Table : da_notification_facture_bl (SQL Server)
-- Notification enregistrée à chaque soumission de facture / BL
-- avec le bilan de la mise à jour de da_afficher.est_facture_bl_soumis
-- ============================================================
CREATE TABLE
    da_notification_facture_bl (
        id INT IDENTITY (1, 1) NOT NULL,
        numero_demande_appro VARCHAR(12) NULL,
        numero_cde VARCHAR(20) NULL,
        numero_livraison VARCHAR(20) NULL,
        numero_or VARCHAR(20) NULL,
        numero_version_da_afficher INT NULL,
        type_soumission VARCHAR(20) NULL,
        montant FLOAT NULL,
        soumis_par VARCHAR(100) NULL,
        nb_articles_livres INT NOT NULL DEFAULT 0,
        nb_lignes_maj INT NOT NULL DEFAULT 0,
        articles_non_trouves VARCHAR(MAX) NULL,
        est_anomalie BIT NOT NULL DEFAULT 0,
        raison_anomalie VARCHAR(1000) NULL,
        est_lu BIT NOT NULL DEFAULT 0,
        date_creation DATETIME2 NOT NULL DEFAULT GETDATE (),
        date_modification DATETIME2 NOT NULL DEFAULT GETDATE (),
        CONSTRAINT PK_da_notification_facture_bl PRIMARY KEY (id)
    );

CREATE INDEX IDX_da_notif_fac_bl_date ON da_notification_facture_bl (date_creation);

CREATE INDEX IDX_da_notif_fac_bl_est_lu ON da_notification_facture_bl (est_lu);
